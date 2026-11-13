<?php

declare(strict_types=1);

namespace Internal\Deptrac\Deptrac;

use Composer\InstalledVersions;
use JetBrains\PHPStormStub\PhpStormStubsMap;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use RuntimeException;

use function array_intersect_key;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function file_get_contents;
use function implode;
use function is_string;
use function ksort;
use function ltrim;
use function preg_match;
use function preg_match_all;
use function preg_split;
use function realpath;
use function sort;
use function sprintf;
use function strtolower;
use function var_export;

/**
 * Derives, from the signatures in jetbrains/phpstorm-stubs, the PHP-internal
 * functions and classes that the `internalIO` collector considers to perform
 * I/O, and renders them as the `InternalIOMap` class that ships with Deptrac.
 *
 * The heuristic: a function or method performs I/O if one of its parameters
 * or its return value is a `resource`. Because `resource` cannot be declared
 * as a type, the stubs only mention it in doc-comments and in
 * `#[LanguageLevelTypeAware]` attributes, which is why this information is
 * not available through reflection.
 *
 * Extensions keep migrating their resources to opaque handle classes (for
 * example `CurlHandle`). Such classes are treated like `resource`, otherwise
 * every one of these migrations would silently shrink the result. They are
 * derived from the stubs as well: a class is a handle if the stubs name it
 * for a parameter or return value that is, or used to be, a `resource`.
 *
 * Deptrac does not track method calls, only dependencies on classes. A class
 * is therefore reported as a whole: if it is a handle, if one of its methods
 * matches the heuristic, or if it inherits from such a class.
 *
 * Run `make internal-io-map` to regenerate the map after updating the stubs.
 */
final class InternalIOMapGenerator
{
    /**
     * Classes that the stubs document next to `resource`, but that are not
     * handles to something outside the PHP process.
     *
     * GMP numbers were resources until PHP 5.6. They are plain numbers, and
     * the stubs offer nothing that tells them apart from real handles.
     *
     * Names are lowercase.
     */
    private const NOT_A_HANDLE = ['gmp'];

    /**
     * Declared types that leave room for a `resource`, which cannot be
     * declared itself. A doc-comment that mentions `resource` for a parameter
     * declared as, say, `string|int` describes PHP versions long gone.
     */
    private const TYPES_THAT_ALLOW_RESOURCES = ['mixed', 'array', 'iterable'];

    /**
     * @var ?array{functions: list<string>, classes: list<string>, handles: list<string>}
     */
    private ?array $map = null;

    /**
     * @param string $stubsDirectory directory the stub files are relative to
     * @param list<string> $stubFiles
     * @param string $stubsVersion version of the stubs, for the record
     */
    public function __construct(
        private readonly string $stubsDirectory,
        private readonly array $stubFiles,
        private readonly string $stubsVersion,
    ) {}

    public static function forInstalledStubs(): self
    {
        $stubFiles = array_values(array_unique(array_merge(
            array_values(PhpStormStubsMap::FUNCTIONS),
            array_values(PhpStormStubsMap::CLASSES),
        )));
        sort($stubFiles);

        return new self(PhpStormStubsMap::DIR, $stubFiles, self::installedStubsVersion());
    }

    /**
     * More than one copy of the stubs can be installed, for example by one of
     * the tools in `tools/`. Only the copy that is read counts.
     */
    private static function installedStubsVersion(): string
    {
        foreach (InstalledVersions::getAllRawData() as $installed) {
            $package = $installed['versions']['jetbrains/phpstorm-stubs'] ?? [];

            if (isset($package['install_path'], $package['pretty_version'])
                && realpath($package['install_path']) === realpath(PhpStormStubsMap::DIR)
            ) {
                return $package['pretty_version'];
            }
        }

        throw new RuntimeException('The version of jetbrains/phpstorm-stubs cannot be determined.');
    }

    /**
     * @return string the source code of the `InternalIOMap` class
     */
    public function generate(): string
    {
        $map = $this->map();

        return <<<PHP
            <?php

            declare(strict_types=1);

            namespace Deptrac\\Deptrac\\DefaultBehavior\\Layer\\Helpers;

            /**
             * PHP-internal functions and classes that are considered to perform I/O
             * because a `resource`, or a handle class that replaced a resource, is part
             * of their signature. Names are lowercase.
             *
             * This file is generated from jetbrains/phpstorm-stubs {$this->stubsVersion}, do not
             * edit it. Run `make internal-io-map` to regenerate it.
             *
             * @internal
             */
            final class InternalIOMap
            {
                public const FUNCTIONS = {$this->export($map['functions'])};

                public const CLASSES = {$this->export($map['classes'])};
            }

            PHP;
    }

    /**
     * @return array{functions: list<string>, classes: list<string>, handles: list<string>} lowercase names
     */
    public function map(): array
    {
        return $this->map ??= $this->buildMap();
    }

    /**
     * @return array{functions: list<string>, classes: list<string>, handles: list<string>}
     */
    private function buildMap(): array
    {
        [$functions, $classes] = $this->signatures();

        $handles = $this->handles($functions, $classes);

        $ioFunctions = [];
        foreach ($functions as $name => $slots) {
            if ($this->performsIO($slots, $handles)) {
                $ioFunctions[] = $name;
            }
        }

        $ioClasses = $handles;
        foreach ($classes as $name => $class) {
            if ($this->performsIO($class['slots'], $handles)) {
                $ioClasses[$name] = true;
            }
        }

        do {
            $grown = false;
            foreach ($classes as $name => $class) {
                if (isset($ioClasses[$name])) {
                    continue;
                }
                foreach ($class['parents'] as $parent) {
                    if (isset($ioClasses[$parent])) {
                        $ioClasses[$name] = true;
                        $grown = true;
                        break;
                    }
                }
            }
        } while ($grown);

        $ioClasses = array_keys($ioClasses);
        $handles = array_keys($handles);
        sort($ioFunctions);
        sort($ioClasses);
        sort($handles);

        return ['functions' => $ioFunctions, 'classes' => $ioClasses, 'handles' => $handles];
    }

    /**
     * Collects, for every function and class in the stubs, the types of its
     * parameters and return values ("slots"). A function that is declared
     * more than once, for different PHP versions, contributes all its slots.
     *
     * @return array{
     *     array<string, list<array{declared: string, documented: string, languageLevels: list<string>}>>,
     *     array<string, array{parents: list<string>, slots: list<array{declared: string, documented: string, languageLevels: list<string>}>}>
     * }
     */
    private function signatures(): array
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $nameResolver = new NodeTraverser();
        $nameResolver->addVisitor(new NameResolver());
        $nodeFinder = new NodeFinder();

        $functions = [];
        $classes = [];

        foreach ($this->stubFiles as $stubFile) {
            $path = $this->stubsDirectory.'/'.$stubFile;
            $code = @file_get_contents($path);
            if (false === $code) {
                throw new RuntimeException(sprintf('Stub file "%s" cannot be read.', $path));
            }

            $nodes = $nameResolver->traverse($parser->parse($code) ?? []);

            /** @var Function_ $function */
            foreach ($nodeFinder->findInstanceOf($nodes, Function_::class) as $function) {
                $name = strtolower((string) $function->namespacedName);
                $functions[$name] = array_merge($functions[$name] ?? [], $this->slots($function));
            }

            /** @var ClassLike $classLike */
            foreach ($nodeFinder->findInstanceOf($nodes, ClassLike::class) as $classLike) {
                if (null === $classLike->namespacedName) {
                    continue;
                }

                $name = strtolower((string) $classLike->namespacedName);
                $classes[$name] ??= ['parents' => [], 'slots' => []];
                $classes[$name]['parents'] = array_merge($classes[$name]['parents'], $this->parents($classLike));
                foreach ($classLike->getMethods() as $method) {
                    $classes[$name]['slots'] = array_merge($classes[$name]['slots'], $this->slots($method));
                }
            }
        }

        return [$functions, $classes];
    }

    /**
     * @return list<string>
     */
    private function parents(ClassLike $classLike): array
    {
        $parents = match (true) {
            $classLike instanceof Class_ => [...(null === $classLike->extends ? [] : [$classLike->extends]), ...$classLike->implements],
            $classLike instanceof Interface_ => $classLike->extends,
            $classLike instanceof Enum_ => $classLike->implements,
            default => [],
        };

        return array_values(array_map(static fn (Node\Name $parent): string => strtolower($parent->toString()), $parents));
    }

    /**
     * @return list<array{declared: string, documented: string, languageLevels: list<string>}>
     */
    private function slots(Function_|ClassMethod $function): array
    {
        $docComment = $function->getDocComment()?->getText() ?? '';

        $documentedParameters = [];
        preg_match_all('/@param\s+(.+?)\s+(?:&|\.\.\.)*\$(\w+)/', $docComment, $matches, PREG_SET_ORDER);
        foreach ($matches as [, $type, $parameter]) {
            $documentedParameters[$parameter] ??= $type;
        }

        $slots = [[
            'declared' => $this->declaredType($function->returnType),
            'documented' => 1 === preg_match('/@return\s+(\S+)/', $docComment, $match) ? $match[1] : '',
            'languageLevels' => $this->languageLevelTypes($function->attrGroups),
        ]];

        foreach ($function->params as $parameter) {
            $name = $parameter->var instanceof Node\Expr\Variable ? $parameter->var->name : null;

            $slots[] = [
                'declared' => $this->declaredType($parameter->type),
                'documented' => is_string($name) ? $documentedParameters[$name] ?? '' : '',
                'languageLevels' => $this->languageLevelTypes($parameter->attrGroups),
            ];
        }

        return $slots;
    }

    private function declaredType(?Node $type): string
    {
        return match (true) {
            null === $type => '',
            $type instanceof Node\NullableType => $this->declaredType($type->type),
            $type instanceof Node\UnionType, $type instanceof Node\IntersectionType => implode('|', array_map($this->declaredType(...), $type->types)),
            $type instanceof Node\Name, $type instanceof Node\Identifier => $type->toString(),
            default => '',
        };
    }

    /**
     * The types named by `#[LanguageLevelTypeAware(['8.0' => 'CurlHandle'], default: 'resource')]`.
     *
     * @param Node\AttributeGroup[] $attributeGroups
     *
     * @return list<string>
     */
    private function languageLevelTypes(array $attributeGroups): array
    {
        $types = [];

        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ('LanguageLevelTypeAware' !== $attribute->name->getLast()) {
                    continue;
                }

                foreach ($attribute->args as $argument) {
                    if ($argument->value instanceof String_) {
                        $types[] = $argument->value->value;
                    }

                    if ($argument->value instanceof Array_) {
                        foreach ($argument->value->items as $item) {
                            if ($item->value instanceof String_) {
                                $types[] = $item->value->value;
                            }
                        }
                    }
                }
            }
        }

        return $types;
    }

    /**
     * @param array<string, list<array{declared: string, documented: string, languageLevels: list<string>}>> $functions
     * @param array<string, array{parents: list<string>, slots: list<array{declared: string, documented: string, languageLevels: list<string>}>}> $classes
     *
     * @return array<string, true>
     */
    private function handles(array $functions, array $classes): array
    {
        $handles = [];

        $allSlots = array_merge(
            [] === $functions ? [] : array_merge(...array_values($functions)),
            ...array_values(array_map(static fn (array $class): array => $class['slots'], $classes)),
        );

        foreach ($allSlots as $slot) {
            $languageLevels = $this->identifiers(...$slot['languageLevels']);
            $documented = $this->documented($slot);

            $candidates = [];
            if (isset($languageLevels['resource'])) {
                $candidates += $languageLevels;
            }
            if (isset($documented['resource'])) {
                $candidates += $documented + $this->identifiers($slot['declared']);
            }

            foreach (array_keys($candidates) as $candidate) {
                if (isset($classes[$candidate])) {
                    $handles[$candidate] = true;
                }
            }
        }

        ksort($handles);

        return $handles;
    }

    /**
     * @param list<array{declared: string, documented: string, languageLevels: list<string>}> $slots
     * @param array<string, true> $handles
     */
    private function performsIO(array $slots, array $handles): bool
    {
        $io = ['resource' => true] + $handles;

        foreach ($slots as $slot) {
            $declared = $this->identifiers($slot['declared']);

            $mentioned = $this->identifiers(...$slot['languageLevels']) + $declared;
            if ($this->allowsResources($declared)) {
                $mentioned += $this->documented($slot);
            }

            if ([] !== array_intersect_key($mentioned, $io)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The names in the documented type of a parameter or return value. A
     * `resource` that is documented next to a class that is not a handle is
     * what that class used to be, and is dropped.
     *
     * @param array{declared: string, documented: string, languageLevels: list<string>} $slot
     *
     * @return array<string, true>
     */
    private function documented(array $slot): array
    {
        $documented = $this->identifiers($slot['documented']);

        foreach (self::NOT_A_HANDLE as $class) {
            if (isset($documented[$class])) {
                unset($documented['resource']);
            }
        }

        return $documented;
    }

    /**
     * @param array<string, true> $declared
     */
    private function allowsResources(array $declared): bool
    {
        if ([] === $declared) {
            return true;
        }

        foreach (self::TYPES_THAT_ALLOW_RESOURCES as $type) {
            if (isset($declared[$type])) {
                return true;
            }
        }

        return false;
    }

    /**
     * The lowercase names a type is made of: `resource` and `false` for
     * `resource|false`, `ftp\connection` for `?\FTP\Connection`.
     *
     * @return array<string, true>
     */
    private function identifiers(string ...$types): array
    {
        $identifiers = [];

        foreach ($types as $type) {
            foreach (preg_split('/[^\w\\\\]+/', $type, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $identifier) {
                $identifiers[strtolower(ltrim($identifier, '\\'))] = true;
            }
        }

        return $identifiers;
    }

    /**
     * @param list<string> $names
     */
    private function export(array $names): string
    {
        if ([] === $names) {
            return '[]';
        }

        $lines = array_map(static fn (string $name): string => sprintf('        %s => true,', var_export($name, true)), $names);

        return "[\n".implode("\n", $lines)."\n    ]";
    }
}

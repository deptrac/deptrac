<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodVisibility;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\DefaultBehavior\Ast\DocParsingHelper;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Reflection\ReflectionProvider;

class PhpStanFileReferenceVisitor extends NodeVisitorAbstract
{
    /** @var PHPStanReferenceExtractorInterface<Node>[] */
    private readonly array $dependencyResolvers;

    private ReferenceBuilder $currentReference;

    /** @var array<int, ReferenceBuilder> reference builders to restore on leave, keyed by spl_object_id of the entered node */
    private array $savedReferences = [];

    private MutatingScope $scope;

    private Lexer $lexer;

    private PhpDocParser $docParser;

    /**
     * @param PHPStanReferenceExtractorInterface<Node> ...$dependencyResolvers
     */
    public function __construct(
        private readonly FileReferenceBuilder $fileReferenceBuilder,
        private readonly ScopeFactory $scopeFactory,
        private readonly ReflectionProvider $reflectionProvider,
        private readonly string $file,
        private readonly bool $collectMethods,
        PHPStanReferenceExtractorInterface ...$dependencyResolvers,
    ) {
        $this->dependencyResolvers = $dependencyResolvers;
        $this->currentReference = $fileReferenceBuilder;
        $this->scope = $this->scopeFactory->create(ScopeContext::create($this->file));
        [$this->lexer, $this->docParser] = DocParsingHelper::create();
    }

    public function enterNode(Node $node)
    {
        match (true) {
            $node instanceof Node\Stmt\Function_ => $this->enterFunction($node),
            $node instanceof ClassLike => $this->enterClassLike($node),
            $node instanceof Node\Stmt\ClassMethod => $this->enterClassMethod($node),
            default => null,
        };

        return null;
    }

    public function leaveNode(Node $node)
    {
        foreach ($this->dependencyResolvers as $resolver) {
            if ($node instanceof ($resolver->getNodeType())) {
                $resolver->processNodeWithPhpStanScope($node, $this->currentReference, $this->scope);
            }
        }

        if ($node instanceof Node\Stmt\ClassMethod && isset($this->savedReferences[spl_object_id($node)])) {
            $this->currentReference = $this->savedReferences[spl_object_id($node)];
            unset($this->savedReferences[spl_object_id($node)]);
        }

        $this->currentReference = match (true) {
            $node instanceof Node\Stmt\Function_ => $this->fileReferenceBuilder,
            $node instanceof ClassLike && null !== $this->getReferenceName($node) => $this->fileReferenceBuilder,
            default => $this->currentReference,
        };

        return null;
    }

    private function enterClassMethod(Node\Stmt\ClassMethod $node): void
    {
        // methods of anonymous classes stay attributed to the enclosing reference
        if ($this->collectMethods && $this->currentReference instanceof ClassLikeReferenceBuilder) {
            $this->savedReferences[spl_object_id($node)] = $this->currentReference;
            $this->currentReference = $this->currentReference->newMethod(
                $node->name->toString(),
                $this->getVisibility($node),
                $node->isStatic(),
                $node->getLine(),
                $this->getTags($node)
            );
        }
    }

    private function getVisibility(Node\Stmt\ClassMethod $node): ClassMethodVisibility
    {
        return match (true) {
            $node->isPrivate() => ClassMethodVisibility::TYPE_PRIVATE,
            $node->isProtected() => ClassMethodVisibility::TYPE_PROTECTED,
            default => ClassMethodVisibility::TYPE_PUBLIC,
        };
    }

    private function enterClassLike(ClassLike $node): void
    {
        $name = $this->getReferenceName($node);
        if (null !== $name) {
            if (!$node instanceof Trait_) {
                $context = ScopeContext::create($this->file)
                    ->enterClass($this->reflectionProvider->getClass($name))
                ;
                $this->scope = $this->scopeFactory->create($context);
            }
            $tags = $this->getTags($node);

            $this->currentReference = match (true) {
                $node instanceof Interface_ => $this->fileReferenceBuilder->newInterface($name, [], $tags),
                $node instanceof Class_ => $this->fileReferenceBuilder->newClass($name, [], $tags),
                $node instanceof Trait_ => $this->fileReferenceBuilder->newTrait($name, [], $tags),
                default => $this->fileReferenceBuilder->newClassLike($name, [], $tags),
            };
        }
    }

    private function enterFunction(Node\Stmt\Function_ $node): void
    {
        $name = $this->getReferenceName($node);
        assert(null !== $name);

        $this->currentReference = $this->fileReferenceBuilder->newFunction($name, [], $this->getTags($node));
    }

    private function getReferenceName(Node\Stmt\Function_|ClassLike $node): ?string
    {
        if (isset($node->namespacedName)) {
            return $node->namespacedName->toCodeString();
        }

        if ($node->name instanceof Identifier) {
            return $node->name->toString();
        }

        return null;
    }

    /**
     * @return array<string,list<string>>
     */
    private function getTags(ClassLike|Node\Stmt\Function_|Node\Stmt\ClassMethod $node): array
    {
        $docComment = $node->getDocComment();
        if (null === $docComment) {
            return [];
        }

        $tokens = new TokenIterator($this->lexer->tokenize($docComment->getText()));
        $docNodeCrate = $this->docParser->parse($tokens);

        $tags = [];
        foreach ($docNodeCrate->getTags() as $tag) {
            $tags[$tag->name][] = (string) $tag->value;
        }

        return $tags;
    }
}

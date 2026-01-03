<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Dependency\DependencyEmitterInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
use Deptrac\Deptrac\Core\Ast\AstLoader;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\AnonymousClassExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ClassExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\FunctionCallExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\FunctionLikeExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\InstanceofExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\NewExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\PropertyExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\StaticCallExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\StaticPropertyFetchExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\TraitUseExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\UseExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\VariableExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use PhpParser\ParserFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait EmitterTrait
{
    /**
     * @param string|string[] $files
     */
    public function getEmittedDependencies(DependencyEmitterInterface $emitter, $files): array
    {
        $files = (array) $files;

        $phpStanContainerDecorator = new PhpStanContainerDecorator(__DIR__, __DIR__, []);
        $typeResolver = new TypeResolver();
        $parser = new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(),
            new AstFileReferenceInMemoryCache(),
            [
                new AnonymousClassExtractor(),
                new FunctionLikeExtractor($typeResolver),
                new PropertyExtractor($phpStanContainerDecorator, $typeResolver),
                new FunctionCallExtractor($typeResolver),
                new VariableExtractor($phpStanContainerDecorator, $typeResolver),
                new ClassExtractor(),
                new UseExtractor(),
                new InstanceofExtractor($typeResolver),
                new StaticCallExtractor($typeResolver),
                new StaticPropertyFetchExtractor($typeResolver),
                new NewExtractor($typeResolver),
                new TraitUseExtractor($typeResolver),
            ]
        );
        $astMap = (new AstLoader($parser, new EventDispatcher()))->createAstMap($files);
        $result = new DependencyList();

        $emitter->applyDependencies($astMap, $result);

        return array_map(
            static fn (DependencyInterface $d) => sprintf('%s:%d on %s',
                $d->getDepender()->toString(),
                $d->getContext()->fileOccurrence->line,
                $d->getDependent()->toString()
            ),
            $result->getDependenciesAndInheritDependencies()
        );
    }
}

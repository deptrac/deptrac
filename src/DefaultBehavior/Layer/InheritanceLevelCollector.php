<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstException;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\AstMapExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;

final class InheritanceLevelCollector implements CollectorInterface
{
    private AstMapInterface $astMap;

    public function __construct(private readonly AstMapExtractorInterface $astMapExtractor) {}

    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if (!$reference instanceof ClassLikeReference) {
            return false;
        }

        try {
            $this->astMap ??= $this->astMapExtractor->extract();
        } catch (AstException $exception) {
            throw CouldNotParseFileException::because('Could not build Ast map', $exception);
        }
        $classInherits = $this->astMap->getClassInherits($reference->getToken());

        if (!isset($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('InheritanceLevelCollector: Missing configuration.');
        }
        if (!is_numeric($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('InheritanceLevelCollector: Configuration is not a number.');
        }

        $depth = (int) $config['value'];
        foreach ($classInherits as $classInherit) {
            if (count($classInherit->getPath()) >= $depth) {
                return true;
            }
        }

        return false;
    }
}

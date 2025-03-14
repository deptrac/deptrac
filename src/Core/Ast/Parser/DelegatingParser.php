<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Core\Ast\Parser\PhpStanParser\PhpStanParser;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use PhpParser\NodeTraverser;

final class DelegatingParser extends AbstractParser
{
    /**
     * @param  array{phpstan_parser: bool, ...}  $featureFlags
     */
    public function __construct(
        private readonly array $featureFlags,
        private readonly NikicPhpParser $nikicPhpParser,
        private readonly PhpStanParser $phpStanParser,
    ) {
        $this->traverser = new NodeTraverser();
    }

    protected function loadNodesFromFile(string $filepath): array
    {
        if ($this->featureFlags['phpstan_parser'] === true) {
            return $this->phpStanParser->loadNodesFromFile($filepath);
        }

        return $this->nikicPhpParser->loadNodesFromFile($filepath);
    }

    public function parseFile(string $file): FileReference
    {
        if ($this->featureFlags['phpstan_parser'] === true) {
            return $this->phpStanParser->parseFile($file);
        }

        return $this->nikicPhpParser->parseFile($file);
    }
}

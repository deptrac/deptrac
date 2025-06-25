<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;

final class FunctionReferenceBuilder extends ReferenceBuilder
{
    /**
     * @param list<string> $tokenTemplates
     * @param array<string,list<string>> $tags
     */
    private function __construct(
        array $tokenTemplates,
        string $filepath,
        private readonly string $functionName,
        private readonly array $tags,
    ) {
        parent::__construct(
            $tokenTemplates,
            $filepath
        );
    }

    /**
     * @param list<string> $functionTemplates
     * @param array<string,list<string>> $tags
     */
    public static function create(string $filepath, string $functionName, array $functionTemplates, array $tags): self
    {
        return new self($functionTemplates, $filepath, $functionName, $tags);
    }

    /** @internal */
    public function build(): FunctionReference
    {
        return new FunctionReference(
            FunctionToken::fromFQCN($this->functionName),
            $this->dependencies,
            $this->tags
        );
    }
}

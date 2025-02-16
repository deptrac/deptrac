<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\AstMap\Function;

use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
class FunctionReferenceBuilder extends ReferenceBuilder
{
    /**
     * @param list<string> $tokenTemplates
     * @param array<string,list<string>> $tags
     */
    private function __construct(array $tokenTemplates, string $filepath, private readonly string $functionName, private readonly array $tags)
    {
        parent::__construct($tokenTemplates, $filepath);
    }
    /**
     * @param list<string> $functionTemplates
     * @param array<string,list<string>> $tags
     */
    public static function create(string $filepath, string $functionName, array $functionTemplates, array $tags) : self
    {
        return new self($functionTemplates, $filepath, $functionName, $tags);
    }
    /** @internal */
    public function build() : \Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionReference
    {
        return new \Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionReference(\Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionToken::fromFQCN($this->functionName), $this->dependencies, $this->tags);
    }
}

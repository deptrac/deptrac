<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\AstMap\Function;

use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TaggedTokenReferenceInterface;
use PHPUnit\Framework\TestCase;
use Tests\Deptrac\Deptrac\Core\Ast\AstMap\TaggedTokenReferenceTestTrait;

final class FunctionReferenceTest extends TestCase
{
    use TaggedTokenReferenceTestTrait;

    private function newWithTags(array $tags): TaggedTokenReferenceInterface
    {
        return new FunctionReference(
            FunctionToken::fromFQCN('testing'),
            [],
            $tags
        );
    }
}

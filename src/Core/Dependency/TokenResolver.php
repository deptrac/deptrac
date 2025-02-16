<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\TokenInterface;
use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileToken;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionReference;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionToken;
use Deptrac\Deptrac\Core\Ast\AstMap\Variable\SuperGlobalToken;
use Deptrac\Deptrac\Core\Ast\AstMap\Variable\VariableReference;
class TokenResolver
{
    /**
     * @throws UnrecognizedTokenException
     */
    public function resolve(TokenInterface $token, AstMap $astMap) : TokenReferenceInterface
    {
        return match (\true) {
            $token instanceof ClassLikeToken => $astMap->getClassReferenceForToken($token) ?? new ClassLikeReference($token),
            $token instanceof FunctionToken => $astMap->getFunctionReferenceForToken($token) ?? new FunctionReference($token),
            $token instanceof SuperGlobalToken => new VariableReference($token),
            $token instanceof FileToken => $astMap->getFileReferenceForToken($token) ?? new FileReference($token->path, [], [], []),
            default => throw \Deptrac\Deptrac\Core\Dependency\UnrecognizedTokenException::cannotCreateReference($token),
        };
    }
}

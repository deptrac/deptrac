<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodVisibility;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Core\Ast\AstMap;

class TokenResolver
{
    /**
     * @throws UnrecognizedTokenException
     */
    public function resolve(TokenInterface $token, AstMap $astMap): TokenReferenceInterface
    {
        return match (true) {
            $token instanceof ClassLikeToken => $astMap->getClassReferenceForToken($token) ?? new ClassLikeReference($token),
            $token instanceof ClassMethodToken => $this->resolveClassMethod($token, $astMap),
            $token instanceof FunctionToken => $astMap->getFunctionReferenceForToken($token) ?? new FunctionReference($token),
            $token instanceof SuperGlobalToken => new VariableReference($token),
            $token instanceof FileToken => $astMap->getFileReferenceForToken($token) ?? new FileReference($token->path, [], [], []),
            default => throw UnrecognizedTokenException::cannotCreateReference($token),
        };
    }

    private function resolveClassMethod(ClassMethodToken $token, AstMap $astMap): ClassMethodReference
    {
        $classReference = $astMap->getClassReferenceForToken($token->getClassToken());

        foreach ($classReference->methods ?? [] as $methodReference) {
            if ($methodReference->getToken()->equals($token)) {
                return $methodReference;
            }
        }

        return new ClassMethodReference($token, ClassMethodVisibility::TYPE_PUBLIC, false);
    }
}

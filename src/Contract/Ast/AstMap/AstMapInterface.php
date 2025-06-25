<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

interface AstMapInterface
{
    /**
     * @return array<string, ClassLikeReference>
     */
    public function getClassLikeReferences(): array;

    /**
     * @return array<string, FileReference>
     */
    public function getFileReferences(): array;

    /**
     * @return array<string, FunctionReference>
     */
    public function getFunctionReferences(): array;

    public function getFunctionReferenceForToken(FunctionToken $tokenName): ?FunctionReference;

    /**
     * @return iterable<AstInherit>
     */
    public function getClassInherits(ClassLikeToken $classLikeName): iterable;
}

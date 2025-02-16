<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\AstMap;

use ArrayObject;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileToken;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionReference;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionToken;
use SplStack;
class AstMap
{
    /**
     * @var array<string, ClassLikeReference>
     */
    private array $classReferences = [];
    /**
     * @var array<string, FileReference>
     */
    private array $fileReferences = [];
    /**
     * @var array<string, FunctionReference>
     */
    private array $functionReferences = [];
    /**
     * @param FileReference[] $astFileReferences
     */
    public function __construct(array $astFileReferences)
    {
        foreach ($astFileReferences as $astFileReference) {
            $this->addAstFileReference($astFileReference);
        }
    }
    /**
     * @return ClassLikeReference[]
     */
    public function getClassLikeReferences() : array
    {
        return $this->classReferences;
    }
    /**
     * @return FileReference[]
     */
    public function getFileReferences() : array
    {
        return $this->fileReferences;
    }
    /**
     * @return FunctionReference[]
     */
    public function getFunctionReferences() : array
    {
        return $this->functionReferences;
    }
    public function getClassReferenceForToken(ClassLikeToken $className) : ?ClassLikeReference
    {
        return $this->classReferences[$className->toString()] ?? null;
    }
    public function getFunctionReferenceForToken(FunctionToken $tokenName) : ?FunctionReference
    {
        return $this->functionReferences[$tokenName->toString()] ?? null;
    }
    public function getFileReferenceForToken(FileToken $tokenName) : ?FileReference
    {
        return $this->fileReferences[$tokenName->toString()] ?? null;
    }
    /**
     * @return iterable<AstInherit>
     */
    public function getClassInherits(ClassLikeToken $classLikeName) : iterable
    {
        $classReference = $this->getClassReferenceForToken($classLikeName);
        if (null === $classReference) {
            return [];
        }
        foreach ($classReference->inherits as $dep) {
            (yield $dep);
            yield from $this->recursivelyResolveDependencies($dep);
        }
    }
    /**
     * @param ArrayObject<string, true>|null $alreadyResolved
     * @param SplStack<AstInherit>|null $pathStack
     *
     * @return iterable<AstInherit>
     */
    private function recursivelyResolveDependencies(\Deptrac\Deptrac\Core\Ast\AstMap\AstInherit $inheritDependency, ?ArrayObject $alreadyResolved = null, ?SplStack $pathStack = null) : iterable
    {
        $alreadyResolved ??= new ArrayObject();
        /** @var ArrayObject<string, true> $alreadyResolved */
        if (null === $pathStack) {
            /** @var SplStack<AstInherit> $pathStack */
            $pathStack = new SplStack();
            $pathStack->push($inheritDependency);
        }
        $className = $inheritDependency->classLikeName->toString();
        if (isset($alreadyResolved[$className])) {
            $pathStack->pop();
            return [];
        }
        $classReference = $this->getClassReferenceForToken($inheritDependency->classLikeName);
        if (null === $classReference) {
            return [];
        }
        foreach ($classReference->inherits as $inherit) {
            $alreadyResolved[$className] = \true;
            /** @var AstInherit[] $path */
            $path = \iterator_to_array($pathStack);
            (yield $inherit->replacePath($path));
            $pathStack->push($inherit);
            yield from $this->recursivelyResolveDependencies($inherit, $alreadyResolved, $pathStack);
            unset($alreadyResolved[$className]);
            $pathStack->pop();
        }
    }
    private function addClassLike(ClassLikeReference $astClassReference) : void
    {
        $this->classReferences[$astClassReference->getToken()->toString()] = $astClassReference;
    }
    private function addAstFileReference(FileReference $astFileReference) : void
    {
        $this->fileReferences[$astFileReference->filepath] = $astFileReference;
        foreach ($astFileReference->classLikeReferences as $astClassReference) {
            $this->addClassLike($astClassReference);
        }
        foreach ($astFileReference->functionReferences as $astFunctionReference) {
            $this->addFunction($astFunctionReference);
        }
    }
    private function addFunction(FunctionReference $astFunctionReference) : void
    {
        $this->functionReferences[$astFunctionReference->getToken()->toString()] = $astFunctionReference;
    }
}

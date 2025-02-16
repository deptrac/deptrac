<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\AstMap\File;

use Deptrac\Deptrac\Contract\Ast\DependencyType;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken;
use Deptrac\Deptrac\Core\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
final class FileReferenceBuilder extends ReferenceBuilder
{
    /** @var ClassLikeReferenceBuilder[] */
    private array $classReferences = [];
    /** @var FunctionReferenceBuilder[] */
    private array $functionReferences = [];
    public static function create(string $filepath) : self
    {
        return new self([], $filepath);
    }
    public function useStatement(string $classLikeName, int $occursAtLine) : self
    {
        $this->dependencies[] = new DependencyToken(ClassLikeToken::fromFQCN($classLikeName), $this->createContext($occursAtLine, DependencyType::USE));
        return $this;
    }
    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newClass(string $classLikeName, array $templateTypes, array $tags) : ClassLikeReferenceBuilder
    {
        $classReference = ClassLikeReferenceBuilder::createClass($this->filepath, $classLikeName, $templateTypes, $tags);
        $this->classReferences[] = $classReference;
        return $classReference;
    }
    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newTrait(string $classLikeName, array $templateTypes, array $tags) : ClassLikeReferenceBuilder
    {
        $classReference = ClassLikeReferenceBuilder::createTrait($this->filepath, $classLikeName, $templateTypes, $tags);
        $this->classReferences[] = $classReference;
        return $classReference;
    }
    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newClassLike(string $classLikeName, array $templateTypes, array $tags) : ClassLikeReferenceBuilder
    {
        $classReference = ClassLikeReferenceBuilder::createClassLike($this->filepath, $classLikeName, $templateTypes, $tags);
        $this->classReferences[] = $classReference;
        return $classReference;
    }
    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newInterface(string $classLikeName, array $templateTypes, array $tags) : ClassLikeReferenceBuilder
    {
        $classReference = ClassLikeReferenceBuilder::createInterface($this->filepath, $classLikeName, $templateTypes, $tags);
        $this->classReferences[] = $classReference;
        return $classReference;
    }
    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newFunction(string $functionName, array $templateTypes = [], array $tags = []) : FunctionReferenceBuilder
    {
        $functionReference = FunctionReferenceBuilder::create($this->filepath, $functionName, $templateTypes, $tags);
        $this->functionReferences[] = $functionReference;
        return $functionReference;
    }
    public function build() : \Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference
    {
        $classReferences = [];
        foreach ($this->classReferences as $classReference) {
            $classReferences[] = $classReference->build();
        }
        $functionReferences = [];
        foreach ($this->functionReferences as $functionReference) {
            $functionReferences[] = $functionReference->build();
        }
        return new \Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference($this->filepath, $classReferences, $functionReferences, $this->dependencies);
    }
}

<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;

final class FileReferenceBuilder extends ReferenceBuilder
{
    /** @var ClassLikeReferenceBuilder[] */
    private array $classReferences = [];

    /** @var FunctionReferenceBuilder[] */
    private array $functionReferences = [];

    public static function create(string $filepath): self
    {
        return new self([], $filepath);
    }

    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newClass(string $classLikeName, array $templateTypes, array $tags): ClassLikeReferenceBuilder
    {
        $classReference = ClassLikeReferenceBuilder::createClass($this->filepath, $classLikeName, $templateTypes, $tags);
        $this->classReferences[] = $classReference;

        return $classReference;
    }

    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newTrait(string $classLikeName, array $templateTypes, array $tags): ClassLikeReferenceBuilder
    {
        $classReference = ClassLikeReferenceBuilder::createTrait($this->filepath, $classLikeName, $templateTypes, $tags);
        $this->classReferences[] = $classReference;

        return $classReference;
    }

    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newClassLike(string $classLikeName, array $templateTypes, array $tags): ClassLikeReferenceBuilder
    {
        $classReference = ClassLikeReferenceBuilder::createClassLike($this->filepath, $classLikeName, $templateTypes, $tags);
        $this->classReferences[] = $classReference;

        return $classReference;
    }

    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newInterface(string $classLikeName, array $templateTypes, array $tags): ClassLikeReferenceBuilder
    {
        $classReference = ClassLikeReferenceBuilder::createInterface($this->filepath, $classLikeName, $templateTypes, $tags);
        $this->classReferences[] = $classReference;

        return $classReference;
    }

    /**
     * @param list<string> $templateTypes
     * @param array<string,list<string>> $tags
     */
    public function newFunction(string $functionName, array $templateTypes = [], array $tags = []): FunctionReferenceBuilder
    {
        $functionReference = FunctionReferenceBuilder::create($this->filepath, $functionName, $templateTypes, $tags);
        $this->functionReferences[] = $functionReference;

        return $functionReference;
    }

    public function build(): FileReference
    {
        $classReferences = [];
        foreach ($this->classReferences as $classReference) {
            $classReferences[] = $classReference->build();
        }

        $functionReferences = [];
        foreach ($this->functionReferences as $functionReference) {
            $functionReferences[] = $functionReference->build();
        }

        return new FileReference($this->filepath, $classReferences, $functionReferences, $this->dependencies);
    }
}

<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodVisibility;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;

final class ClassLikeReferenceBuilder extends ReferenceBuilder
{
    /** @var ClassMethodReferenceBuilder[] */
    private array $methodReferences = [];

    /**
     * @param list<string> $tokenTemplates
     * @param array<string,list<string>> $tags
     */
    private function __construct(
        array $tokenTemplates,
        string $filepath,
        private readonly ClassLikeToken $classLikeToken,
        private readonly ClassLikeType $classLikeType,
        private readonly array $tags,
    ) {
        parent::__construct($tokenTemplates, $filepath);
    }

    /**
     * @param list<string> $classTemplates
     * @param array<string,list<string>> $tags
     */
    public static function createClassLike(string $filepath, string $classLikeName, array $classTemplates, array $tags): self
    {
        return new self($classTemplates, $filepath, ClassLikeToken::fromFQCN($classLikeName), ClassLikeType::TYPE_CLASSLIKE, $tags);
    }

    /**
     * @param list<string> $classTemplates
     * @param array<string,list<string>> $tags
     */
    public static function createClass(string $filepath, string $classLikeName, array $classTemplates, array $tags): self
    {
        return new self($classTemplates, $filepath, ClassLikeToken::fromFQCN($classLikeName), ClassLikeType::TYPE_CLASS, $tags);
    }

    /**
     * @param list<string> $classTemplates
     * @param array<string,list<string>> $tags
     */
    public static function createTrait(string $filepath, string $classLikeName, array $classTemplates, array $tags): self
    {
        return new self($classTemplates, $filepath, ClassLikeToken::fromFQCN($classLikeName), ClassLikeType::TYPE_TRAIT, $tags);
    }

    /**
     * @param list<string> $classTemplates
     * @param array<string,list<string>> $tags
     */
    public static function createInterface(string $filepath, string $classLikeName, array $classTemplates, array $tags): self
    {
        return new self($classTemplates, $filepath, ClassLikeToken::fromFQCN($classLikeName), ClassLikeType::TYPE_INTERFACE, $tags);
    }

    public function getClassToken(): ClassLikeToken
    {
        return $this->classLikeToken;
    }

    /**
     * @param array<string,list<string>> $tags
     */
    public function newMethod(string $methodName, ClassMethodVisibility $visibility, bool $isStatic, int $declaredAtLine, array $tags): ClassMethodReferenceBuilder
    {
        $methodReference = ClassMethodReferenceBuilder::create(
            $this->tokenTemplateLikes,
            $this->filepath,
            $this,
            ClassMethodToken::fromClassTokenAndMethodName($this->classLikeToken, $methodName),
            $visibility,
            $isStatic,
            $declaredAtLine,
            $tags
        );
        $this->methodReferences[] = $methodReference;

        return $methodReference;
    }

    /** @internal mirrors a method dependency on the owning class-like reference */
    public function addDependencyToken(DependencyToken $dependency): void
    {
        $this->dependencies[] = $dependency;
    }

    /** @internal */
    public function build(): ClassLikeReference
    {
        $methodReferences = [];
        foreach ($this->methodReferences as $methodReference) {
            $methodReferences[] = $methodReference->build();
        }

        return new ClassLikeReference(
            $this->classLikeToken,
            $this->classLikeType,
            $this->inherits,
            $this->dependencies,
            $this->tags,
            null,
            $methodReferences
        );
    }
}

<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;

final class ClassLikeReferenceBuilder extends ReferenceBuilder
{
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

    /** @internal */
    public function build(): ClassLikeReference
    {
        return new ClassLikeReference(
            $this->classLikeToken,
            $this->classLikeType,
            $this->inherits,
            $this->dependencies,
            $this->tags
        );
    }
}

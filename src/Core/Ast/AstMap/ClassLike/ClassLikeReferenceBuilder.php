<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\AstMap\ClassLike;

use Deptrac\Deptrac\Contract\Ast\FileOccurrence;
use Deptrac\Deptrac\Core\Ast\AstMap\AstInherit;
use Deptrac\Deptrac\Core\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
final class ClassLikeReferenceBuilder extends ReferenceBuilder
{
    /** @var AstInherit[] */
    private array $inherits = [];
    /**
     * @param list<string> $tokenTemplates
     * @param array<string,list<string>> $tags
     */
    private function __construct(array $tokenTemplates, string $filepath, private readonly \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken $classLikeToken, private readonly \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType $classLikeType, private readonly array $tags)
    {
        parent::__construct($tokenTemplates, $filepath);
    }
    /**
     * @param list<string> $classTemplates
     * @param array<string,list<string>> $tags
     */
    public static function createClassLike(string $filepath, string $classLikeName, array $classTemplates, array $tags) : self
    {
        return new self($classTemplates, $filepath, \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken::fromFQCN($classLikeName), \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType::TYPE_CLASSLIKE, $tags);
    }
    /**
     * @param list<string> $classTemplates
     * @param array<string,list<string>> $tags
     */
    public static function createClass(string $filepath, string $classLikeName, array $classTemplates, array $tags) : self
    {
        return new self($classTemplates, $filepath, \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken::fromFQCN($classLikeName), \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType::TYPE_CLASS, $tags);
    }
    /**
     * @param list<string> $classTemplates
     * @param array<string,list<string>> $tags
     */
    public static function createTrait(string $filepath, string $classLikeName, array $classTemplates, array $tags) : self
    {
        return new self($classTemplates, $filepath, \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken::fromFQCN($classLikeName), \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType::TYPE_TRAIT, $tags);
    }
    /**
     * @param list<string> $classTemplates
     * @param array<string,list<string>> $tags
     */
    public static function createInterface(string $filepath, string $classLikeName, array $classTemplates, array $tags) : self
    {
        return new self($classTemplates, $filepath, \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken::fromFQCN($classLikeName), \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType::TYPE_INTERFACE, $tags);
    }
    /** @internal */
    public function build() : \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference
    {
        return new \Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference($this->classLikeToken, $this->classLikeType, $this->inherits, $this->dependencies, $this->tags);
    }
    public function extends(string $classLikeName, int $occursAtLine) : self
    {
        $this->inherits[] = new AstInherit(\Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken::fromFQCN($classLikeName), new FileOccurrence($this->filepath, $occursAtLine), AstInheritType::EXTENDS);
        return $this;
    }
    public function implements(string $classLikeName, int $occursAtLine) : self
    {
        $this->inherits[] = new AstInherit(\Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken::fromFQCN($classLikeName), new FileOccurrence($this->filepath, $occursAtLine), AstInheritType::IMPLEMENTS);
        return $this;
    }
    public function trait(string $classLikeName, int $occursAtLine) : self
    {
        $this->inherits[] = new AstInherit(\Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken::fromFQCN($classLikeName), new FileOccurrence($this->filepath, $occursAtLine), AstInheritType::USES);
        return $this;
    }
}

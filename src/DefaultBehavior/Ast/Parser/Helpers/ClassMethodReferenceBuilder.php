<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodVisibility;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;

final class ClassMethodReferenceBuilder extends ReferenceBuilder
{
    /**
     * @param list<string> $tokenTemplates
     * @param array<string,list<string>> $tags
     */
    private function __construct(
        array $tokenTemplates,
        string $filepath,
        private readonly ClassLikeReferenceBuilder $classReferenceBuilder,
        private readonly ClassMethodToken $methodToken,
        private readonly ClassMethodVisibility $visibility,
        private readonly bool $isStatic,
        private readonly int $declaredAtLine,
        private readonly array $tags,
    ) {
        parent::__construct($tokenTemplates, $filepath);
    }

    /**
     * @param list<string> $tokenTemplates
     * @param array<string,list<string>> $tags
     */
    public static function create(
        array $tokenTemplates,
        string $filepath,
        ClassLikeReferenceBuilder $classReferenceBuilder,
        ClassMethodToken $methodToken,
        ClassMethodVisibility $visibility,
        bool $isStatic,
        int $declaredAtLine,
        array $tags,
    ): self {
        return new self($tokenTemplates, $filepath, $classReferenceBuilder, $methodToken, $visibility, $isStatic, $declaredAtLine, $tags);
    }

    public function getMethodToken(): ClassMethodToken
    {
        return $this->methodToken;
    }

    /**
     * Method dependencies are recorded on the method and mirrored on the owning
     * class-like reference, so class-level analysis stays complete.
     */
    public function dependency(TokenInterface $token, int $occursAtLine, DependencyType $type): static
    {
        $dependency = new DependencyToken($token, $this->createContext($occursAtLine, $type));
        $this->dependencies[] = $dependency;
        $this->classReferenceBuilder->addDependencyToken($dependency);

        return $this;
    }

    /** @internal */
    public function build(): ClassMethodReference
    {
        return new ClassMethodReference(
            $this->methodToken,
            $this->visibility,
            $this->isStatic,
            new FileOccurrence($this->filepath, $this->declaredAtLine),
            $this->dependencies,
            $this->tags
        );
    }
}

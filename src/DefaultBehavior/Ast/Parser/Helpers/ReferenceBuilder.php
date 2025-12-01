<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstInherit;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;

abstract class ReferenceBuilder implements ReferenceBuilderInterface
{
    /** @var AstInherit[] */
    protected array $inherits = [];

    /** @var DependencyToken[] */
    protected array $dependencies = [];

    /**
     * @param list<string> $tokenTemplateLikes
     */
    protected function __construct(protected array $tokenTemplateLikes, protected string $filepath) {}

    final public function getTokenTemplateLikes(): array
    {
        return $this->tokenTemplateLikes;
    }

    protected function createContext(int $occursAtLine, DependencyType $type): DependencyContext
    {
        return new DependencyContext(new FileOccurrence($this->filepath, $occursAtLine), $type);
    }

    public function dependency(TokenInterface $token, int $occursAtLine, DependencyType $type): static
    {
        $this->dependencies[] = new DependencyToken($token, $this->createContext($occursAtLine, $type));

        return $this;
    }

    public function astInherits(TokenInterface $token, int $occursAtLine, AstInheritType $type): static
    {
        $this->inherits[] = new AstInherit($token, new FileOccurrence($this->filepath, $occursAtLine), $type);

        return $this;
    }

    public function addTokenTemplateLike(string $tokenTemplateLike): void
    {
        $this->tokenTemplateLikes[] = $tokenTemplateLike;
    }

    public function removeTokenTemplateLike(string $tokenTemplateLike): void
    {
        $key = array_search($tokenTemplateLike, $this->tokenTemplateLikes, true);
        if (false !== $key) {
            unset($this->tokenTemplateLikes[$key]);
        }
    }
}

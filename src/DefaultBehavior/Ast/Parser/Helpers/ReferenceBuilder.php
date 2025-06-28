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
     * @param list<string> $tokenTemplates
     */
    protected function __construct(protected array $tokenTemplates, protected string $filepath) {}

    final public function getTokenTemplates(): array
    {
        return $this->tokenTemplates;
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

    public function addTokenTemplate(string $tokenTemplate): void
    {
        $this->tokenTemplates[] = $tokenTemplate;
    }

    public function removeTokenTemplate(string $tokenTemplate): void
    {
        $key = array_search($tokenTemplate, $this->tokenTemplates, true);
        if (false !== $key) {
            unset($this->tokenTemplates[$key]);
        }
    }
}

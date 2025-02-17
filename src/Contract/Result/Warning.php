<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Result;

use Stringable;
/**
 * @psalm-immutable
 *
 * Represents a situation that, while valid, is not recommended. This can be
 * used to guide the end-user to a more proper solution.
 */
final class Warning implements Stringable
{
    public function __construct(private readonly string $message)
    {
    }
    /**
     * @param string[] $layerNames
     */
    public static function tokenIsInMoreThanOneLayer(string $tokenName, array $layerNames) : self
    {
        return new self(\sprintf('%s is in more than one layer ["%s"]. It is recommended that one token should only be in one layer.', $tokenName, \implode('", "', $layerNames)));
    }
    public function __toString() : string
    {
        return $this->message;
    }
}

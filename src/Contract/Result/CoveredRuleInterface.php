<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Result;

/**
 * @psalm-immutable
 *
 * Represents a dependency that is covered by the defined rules.
 *
 * This does not mean that it is allowed to exist, just that it is covered.
 * In that sense it exists as a complement to `Uncovered` class
 */
interface CoveredRuleInterface extends \Deptrac\Deptrac\Contract\Result\RuleInterface
{
    public function getDependerLayer() : string;
    public function getDependentLayer() : string;
}

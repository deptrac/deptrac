<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Analyser;

use DEPTRAC_INTERNAL\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Every rule that can create a Violation has to implement this interface.
 *
 * It is used for output processing to display what rule has been violated.
 */
interface ViolationCreatingInterface extends EventSubscriberInterface
{
    /**
     * @psalm-pure
     */
    public function ruleName() : string;
    /**
     * @psalm-pure
     */
    public function ruleDescription() : string;
}

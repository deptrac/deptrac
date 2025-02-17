<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Dependency;

use DEPTRAC_INTERNAL\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event triggered before all the dependencies have been flattened.
 *
 * This occurs when all dependencies caused by class inheritance have been resolved.
 */
final class PreFlattenEvent extends Event
{
}

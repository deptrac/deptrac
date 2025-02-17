<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Dependency;

use DEPTRAC_INTERNAL\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event triggered after all the dependencies have been resolved.
 */
final class PostEmitEvent extends Event
{
}

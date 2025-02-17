<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract;

use Throwable;
/**
 * Shared interface for all Exceptions that Deptrac can possibly throw.
 *
 * You can use this to ensure that no exceptions go unhandled when integrating
 * with Deptrac codebase.
 */
interface ExceptionInterface extends Throwable
{
}

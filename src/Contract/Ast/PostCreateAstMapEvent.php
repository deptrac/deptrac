<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Ast;

use DEPTRAC_INTERNAL\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event triggered after the AST map of all files has been created.
 */
final class PostCreateAstMapEvent extends Event
{
}

<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Ast;

use DEPTRAC_202404\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event triggered after the AST map of all files has been created.
 */
final class PostCreateAstMapEvent extends Event
{
}

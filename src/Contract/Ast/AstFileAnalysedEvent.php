<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Ast;

use DEPTRAC_INTERNAL\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event triggered after parsing the AST of a file has been completed.
 */
final class AstFileAnalysedEvent extends Event
{
    public function __construct(public readonly string $file)
    {
    }
}

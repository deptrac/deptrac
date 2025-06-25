<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * Type of Ast Inheritance between class-likes.
 *
 * @see AstInherit
 */
enum AstInheritType: string
{
    case EXTENDS = 'Extends';
    case IMPLEMENTS = 'Implements';
    case USES = 'Uses';
}

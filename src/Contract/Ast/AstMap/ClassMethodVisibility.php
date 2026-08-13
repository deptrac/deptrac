<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

enum ClassMethodVisibility: string
{
    case TYPE_PUBLIC = 'public';
    case TYPE_PROTECTED = 'protected';
    case TYPE_PRIVATE = 'private';
}

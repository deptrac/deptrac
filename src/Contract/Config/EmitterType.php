<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Config;

enum EmitterType : string
{
    case CLASS_TOKEN = 'class';
    case CLASS_SUPERGLOBAL_TOKEN = 'class_superglobal';
    case FILE_TOKEN = 'file';
    case FUNCTION_TOKEN = 'function';
    case FUNCTION_CALL = 'function_call';
    case FUNCTION_SUPERGLOBAL_TOKEN = 'function_superglobal';
    case USE_TOKEN = 'use';
    /**
     * @return list<string>
     */
    public static function values() : array
    {
        return \array_map(static fn(self $type): string => $type->value, self::cases());
    }
}

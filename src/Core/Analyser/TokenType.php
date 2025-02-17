<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser;

use Deptrac\Deptrac\Contract\Config\EmitterType;
enum TokenType : string
{
    case CLASS_LIKE = 'class-like';
    case FUNCTION = 'function';
    case FILE = 'file';
    public static function tryFromEmitterType(EmitterType $emitterType) : ?self
    {
        return EmitterType::CLASS_TOKEN === $emitterType ? self::CLASS_LIKE : self::tryFrom($emitterType->value);
    }
}

<?php

namespace Deptrac\Deptrac\Contract\Config;

enum CodeclimateLevelEnum : string
{
    case INFO = 'info';
    case MINOR = 'minor';
    case MAJOR = 'major';
    case CRITICAL = 'critical';
    case BLOCKER = 'blocker';
}

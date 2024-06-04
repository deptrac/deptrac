<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\Swoole\Server;

class Event
{
    public $reactor_id = 0;
    public $fd = 0;
    public $dispatch_time = 0;
    public $data;
}

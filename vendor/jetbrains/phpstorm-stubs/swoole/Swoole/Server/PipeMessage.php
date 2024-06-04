<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\Swoole\Server;

class PipeMessage
{
    public $source_worker_id = 0;
    public $dispatch_time = 0;
    public $data;
}

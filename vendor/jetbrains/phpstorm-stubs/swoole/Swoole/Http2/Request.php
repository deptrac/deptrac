<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\Swoole\Http2;

class Request
{
    public $path = '/';
    public $method = 'GET';
    public $headers;
    public $cookies;
    public $data = '';
    public $pipeline = \false;
}

<?php

namespace DEPTRAC_INTERNAL;

/**
 * @since 8.1
 */
class CURLStringFile
{
    public string $data;
    public string $mime;
    public string $postname;
    public function __construct(string $data, string $postname, string $mime = 'application/octet-stream')
    {
    }
}
/**
 * @since 8.1
 */
\class_alias('DEPTRAC_INTERNAL\\CURLStringFile', 'CURLStringFile', \false);

<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Config;

/** @psalm-suppress PropertyNotSetInConstructor false positive */
abstract class ConfigurableCollectorConfig extends \Deptrac\Deptrac\Contract\Config\CollectorConfig
{
    private const ESCAPEES = ['\\', '\\\\', '\\"', '"', "\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08", "\t", "\n", "\v", "\f", "\r", "\x0e", "\x0f", "\x10", "\x11", "\x12", "\x13", "\x14", "\x15", "\x16", "\x17", "\x18", "\x19", "\x1a", "\x1b", "\x1c", "\x1d", "\x1e", "\x1f", "", "", " ", " ", " "];
    private const ESCAPED = ['\\\\', '\\"', '\\\\', '\\"', '\\0', '\\x01', '\\x02', '\\x03', '\\x04', '\\x05', '\\x06', '\\a', '\\b', '\\t', '\\n', '\\v', '\\f', '\\r', '\\x0e', '\\x0f', '\\x10', '\\x11', '\\x12', '\\x13', '\\x14', '\\x15', '\\x16', '\\x17', '\\x18', '\\x19', '\\x1a', '\\e', '\\x1c', '\\x1d', '\\x1e', '\\x1f', '\\x7f', '\\N', '\\_', '\\L', '\\P'];
    protected final function __construct(protected string $config)
    {
    }
    public static function create(string $config) : self
    {
        return new static(self::regex($config));
    }
    /**
     * @return array{private: bool, type: string, value: string}
     */
    public function toArray() : array
    {
        return ['value' => $this->config, 'type' => $this->collectorType->value, 'private' => $this->private];
    }
    private static function regex(string $regex) : string
    {
        return \sprintf('%s', \str_replace(self::ESCAPEES, self::ESCAPED, $regex));
    }
}

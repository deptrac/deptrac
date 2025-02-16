<?php

namespace Deptrac\Deptrac\Contract\Config\Collector;

use Deptrac\Deptrac\Contract\Config\CollectorConfig;
use Deptrac\Deptrac\Contract\Config\CollectorType;
final class TagValueRegexConfig extends CollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_TAG_VALUE_REGEX;
    public function __construct(private string $tag, private ?string $value = null)
    {
    }
    public static function create(string $tag, ?string $regexpr = null) : self
    {
        return new self($tag, $regexpr);
    }
    public function match(string $regexpr) : self
    {
        $this->value = $regexpr;
        return $this;
    }
    public function toArray() : array
    {
        return ['tag' => $this->tag, 'value' => $this->value] + parent::toArray();
    }
}

<?php

namespace Deptrac\Deptrac\Contract\Config\Formatter;

use Deptrac\Deptrac\Contract\Config\CodeclimateLevelEnum;
final class CodeclimateConfig implements \Deptrac\Deptrac\Contract\Config\Formatter\FormatterConfigInterface
{
    private function __construct(private CodeclimateLevelEnum $failure, private CodeclimateLevelEnum $skipped, private CodeclimateLevelEnum $uncovered)
    {
    }
    public static function create(CodeclimateLevelEnum $failure = CodeclimateLevelEnum::BLOCKER, CodeclimateLevelEnum $skipped = CodeclimateLevelEnum::MINOR, CodeclimateLevelEnum $uncovered = CodeclimateLevelEnum::INFO) : self
    {
        return new self($failure, $skipped, $uncovered);
    }
    public function severity(CodeclimateLevelEnum $failure = CodeclimateLevelEnum::BLOCKER, CodeclimateLevelEnum $skipped = CodeclimateLevelEnum::MINOR, CodeclimateLevelEnum $uncovered = CodeclimateLevelEnum::INFO) : static
    {
        $this->failure = $failure;
        $this->skipped = $skipped;
        $this->uncovered = $uncovered;
        return $this;
    }
    /**
     * @return array{'severity': array{'failure': string, 'skipped': string, 'uncovered': string}}
     */
    public function toArray() : array
    {
        return ['severity' => ['failure' => $this->failure->value, 'skipped' => $this->skipped->value, 'uncovered' => $this->uncovered->value]];
    }
    public function getName() : string
    {
        return 'codeclimate';
    }
}

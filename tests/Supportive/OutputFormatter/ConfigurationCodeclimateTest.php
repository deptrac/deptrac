<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\OutputFormatter;

use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers\ConfigurationCodeclimate;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers\ConfigurationCodeclimate
 */
final class ConfigurationCodeclimateTest extends TestCase
{
    public function testFromArray(): void
    {
        $arr = [
            'severity' => [
                'failure' => 'blocker',
                'skipped' => 'critical',
                'uncovered' => 'info',
            ],
        ];
        $config = ConfigurationCodeclimate::fromArray($arr);

        self::assertSame('blocker', $config->getSeverity('failure'));
        self::assertSame('critical', $config->getSeverity('skipped'));
        self::assertSame('info', $config->getSeverity('uncovered'));
    }
}

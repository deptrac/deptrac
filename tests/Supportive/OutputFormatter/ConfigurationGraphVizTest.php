<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\OutputFormatter;

use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers\ConfigurationGraphViz;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers\ConfigurationGraphViz
 */
final class ConfigurationGraphVizTest extends TestCase
{
    public function testFromArray(): void
    {
        $hiddenLayers = ['hidden'];
        $groups = [
            'groupName' => [
                'layer1',
                'layer2',
            ],
        ];
        $pointToGroups = true;
        $arr = [
            'hidden_layers' => $hiddenLayers,
            'groups' => $groups,
            'point_to_groups' => $pointToGroups,
        ];
        $configurationGraphViz = ConfigurationGraphViz::fromArray($arr);

        self::assertSame($hiddenLayers, $configurationGraphViz->hiddenLayers);
        self::assertSame($groups, $configurationGraphViz->groupsLayerMap);
        self::assertSame($pointToGroups, $configurationGraphViz->pointToGroups);
    }
}

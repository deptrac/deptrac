<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Attribute;

use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\ContainerInterface;
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsDecorator
{
    public function __construct(public string $decorates, public int $priority = 0, public int $onInvalid = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
    }
}

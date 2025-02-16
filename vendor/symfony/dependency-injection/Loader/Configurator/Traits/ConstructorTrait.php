<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait ConstructorTrait
{
    /**
     * Sets a static constructor.
     *
     * @return $this
     */
    public final function constructor(string $constructor) : static
    {
        $this->definition->setFactory([null, $constructor]);
        return $this;
    }
}

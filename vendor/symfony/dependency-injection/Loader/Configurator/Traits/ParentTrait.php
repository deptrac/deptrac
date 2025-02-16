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

use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\ChildDefinition;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
trait ParentTrait
{
    /**
     * Sets the Definition to inherit from.
     *
     * @return $this
     *
     * @throws InvalidArgumentException when parent cannot be set
     */
    public final function parent(string $parent) : static
    {
        if (!$this->allowParent) {
            throw new InvalidArgumentException(\sprintf('A parent cannot be defined when either "_instanceof" or "_defaults" are also defined for service prototype "%s".', $this->id));
        }
        if ($this->definition instanceof ChildDefinition) {
            $this->definition->setParent($parent);
        } else {
            // cast Definition to ChildDefinition
            $definition = \serialize($this->definition);
            $definition = \substr_replace($definition, '70', 2, 2);
            $definition = \substr_replace($definition, 'Child', 61, 0);
            $definition = \unserialize($definition);
            $this->definition = $definition->setParent($parent);
        }
        return $this;
    }
}

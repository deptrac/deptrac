<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\Configurator;

use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\ContainerBuilder;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use DEPTRAC_INTERNAL\Symfony\Component\ExpressionLanguage\Expression;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ParametersConfigurator extends AbstractConfigurator
{
    public const FACTORY = 'parameters';
    private ContainerBuilder $container;
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }
    /**
     * @return $this
     */
    public final function set(string $name, mixed $value) : static
    {
        if ($value instanceof Expression) {
            throw new InvalidArgumentException(\sprintf('Using an expression in parameter "%s" is not allowed.', $name));
        }
        $this->container->setParameter($name, static::processValue($value, \true));
        return $this;
    }
    /**
     * @return $this
     */
    public final function __invoke(string $name, mixed $value) : static
    {
        return $this->set($name, $value);
    }
}

<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\OutputFormatter;

use DEPTRAC_INTERNAL\Psr\Container\ContainerInterface;
use Qossmic\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Qossmic\Deptrac\Supportive\DependencyInjection\Exception\InvalidServiceInLocatorException;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\ServiceLocator;
use function array_keys;
use function get_debug_type;
final class FormatterProvider implements ContainerInterface
{
    /**
     * @param ServiceLocator<mixed> $formatterLocator
     */
    public function __construct(private readonly ServiceLocator $formatterLocator)
    {
    }
    public function get(string $id) : OutputFormatterInterface
    {
        $service = $this->formatterLocator->get($id);
        if (!$service instanceof OutputFormatterInterface) {
            throw InvalidServiceInLocatorException::invalidType($id, OutputFormatterInterface::class, get_debug_type($service));
        }
        return $service;
    }
    public function has(string $id) : bool
    {
        return $this->formatterLocator->has($id);
    }
    /**
     * @psalm-suppress MixedReturnTypeCoercion
     *
     * @return string[]
     */
    public function getKnownFormatters() : array
    {
        return array_keys($this->formatterLocator->getProvidedServices());
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DEPTRAC_INTERNAL\Symfony\Component\Console\Descriptor;

use DEPTRAC_INTERNAL\Symfony\Component\Console\Application;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Command\Command;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Exception\InvalidArgumentException;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputArgument;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputDefinition;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputOption;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
abstract class Descriptor implements DescriptorInterface
{
    protected OutputInterface $output;
    public function describe(OutputInterface $output, object $object, array $options = []) : void
    {
        $this->output = $output;
        match (\true) {
            $object instanceof InputArgument => $this->describeInputArgument($object, $options),
            $object instanceof InputOption => $this->describeInputOption($object, $options),
            $object instanceof InputDefinition => $this->describeInputDefinition($object, $options),
            $object instanceof Command => $this->describeCommand($object, $options),
            $object instanceof Application => $this->describeApplication($object, $options),
            default => throw new InvalidArgumentException(\sprintf('Object of type "%s" is not describable.', \get_debug_type($object))),
        };
    }
    protected function write(string $content, bool $decorated = \false) : void
    {
        $this->output->write($content, \false, $decorated ? OutputInterface::OUTPUT_NORMAL : OutputInterface::OUTPUT_RAW);
    }
    /**
     * Describes an InputArgument instance.
     */
    protected abstract function describeInputArgument(InputArgument $argument, array $options = []) : void;
    /**
     * Describes an InputOption instance.
     */
    protected abstract function describeInputOption(InputOption $option, array $options = []) : void;
    /**
     * Describes an InputDefinition instance.
     */
    protected abstract function describeInputDefinition(InputDefinition $definition, array $options = []) : void;
    /**
     * Describes a Command instance.
     */
    protected abstract function describeCommand(Command $command, array $options = []) : void;
    /**
     * Describes an Application instance.
     */
    protected abstract function describeApplication(Application $application, array $options = []) : void;
}

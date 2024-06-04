<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DEPTRAC_INTERNAL\Symfony\Component\Console\Helper;

use DEPTRAC_INTERNAL\Symfony\Component\Console\Descriptor\DescriptorInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Descriptor\JsonDescriptor;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Descriptor\MarkdownDescriptor;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Descriptor\ReStructuredTextDescriptor;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Descriptor\TextDescriptor;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Descriptor\XmlDescriptor;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Exception\InvalidArgumentException;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Output\OutputInterface;
/**
 * This class adds helper method to describe objects in various formats.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class DescriptorHelper extends Helper
{
    /**
     * @var DescriptorInterface[]
     */
    private array $descriptors = [];
    public function __construct()
    {
        $this->register('txt', new TextDescriptor())->register('xml', new XmlDescriptor())->register('json', new JsonDescriptor())->register('md', new MarkdownDescriptor())->register('rst', new ReStructuredTextDescriptor());
    }
    /**
     * Describes an object if supported.
     *
     * Available options are:
     * * format: string, the output format name
     * * raw_text: boolean, sets output type as raw
     *
     * @return void
     *
     * @throws InvalidArgumentException when the given format is not supported
     */
    public function describe(OutputInterface $output, ?object $object, array $options = [])
    {
        $options = \array_merge(['raw_text' => \false, 'format' => 'txt'], $options);
        if (!isset($this->descriptors[$options['format']])) {
            throw new InvalidArgumentException(\sprintf('Unsupported format "%s".', $options['format']));
        }
        $descriptor = $this->descriptors[$options['format']];
        $descriptor->describe($output, $object, $options);
    }
    /**
     * Registers a descriptor.
     *
     * @return $this
     */
    public function register(string $format, DescriptorInterface $descriptor) : static
    {
        $this->descriptors[$format] = $descriptor;
        return $this;
    }
    public function getName() : string
    {
        return 'descriptor';
    }
    public function getFormats() : array
    {
        return \array_keys($this->descriptors);
    }
}

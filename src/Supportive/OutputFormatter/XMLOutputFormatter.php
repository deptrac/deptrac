<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use DOMAttr;
use DOMDocument;
use DOMElement;
use Exception;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Violation;
/**
 * @internal
 */
final class XMLOutputFormatter implements OutputFormatterInterface
{
    private const DEFAULT_PATH = './deptrac-report.xml';
    public static function getName() : string
    {
        return 'xml';
    }
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function finish(OutputResult $result, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        $xml = $this->createXml($result);
        $dumpXmlPath = $outputFormatterInput->outputPath ?? self::DEFAULT_PATH;
        \file_put_contents($dumpXmlPath, $xml);
        $output->writeLineFormatted('<info>XML Report dumped to ' . \realpath($dumpXmlPath) . '</info>');
    }
    /**
     * @throws Exception
     */
    private function createXml(OutputResult $dependencyContext) : string
    {
        if (!\class_exists(DOMDocument::class)) {
            throw new Exception('Unable to create xml file (php-xml needs to be installed)');
        }
        $xmlDoc = new DOMDocument('1.0', 'UTF-8');
        $xmlDoc->formatOutput = \true;
        $rootEntry = $xmlDoc->createElement('entries');
        foreach ($dependencyContext->violations() as $rule) {
            $this->addRule('violation', $rootEntry, $xmlDoc, $rule);
        }
        foreach ($dependencyContext->skippedViolations() as $rule) {
            $this->addRule('skipped_violation', $rootEntry, $xmlDoc, $rule);
        }
        $xmlDoc->appendChild($rootEntry);
        return (string) $xmlDoc->saveXML();
    }
    private function addRule(string $type, DOMElement $rootEntry, DOMDocument $xmlDoc, Violation|SkippedViolation $rule) : void
    {
        /** @throws void */
        $entry = $xmlDoc->createElement('entry');
        /** @throws void */
        $entry->appendChild(new DOMAttr('type', $type));
        /** @throws void */
        $entry->appendChild($xmlDoc->createElement('LayerA', $rule->getDependerLayer()));
        /** @throws void */
        $entry->appendChild($xmlDoc->createElement('LayerB', $rule->getDependentLayer()));
        $dependency = $rule->getDependency();
        /** @throws void */
        $entry->appendChild($xmlDoc->createElement('ClassA', $dependency->getDepender()->toString()));
        /** @throws void */
        $entry->appendChild($xmlDoc->createElement('ClassB', $dependency->getDependent()->toString()));
        $fileOccurrence = $dependency->getContext()->fileOccurrence;
        /** @throws void */
        $occurrence = $xmlDoc->createElement('occurrence');
        $occurrence->setAttribute('file', $fileOccurrence->filepath);
        $occurrence->setAttribute('line', (string) $fileOccurrence->line);
        $entry->appendChild($occurrence);
        $rootEntry->appendChild($entry);
    }
}

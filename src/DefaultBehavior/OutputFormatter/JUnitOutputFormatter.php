<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\OutputFormatter;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\CoveredRuleInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\RuleInterface;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Contract\Result\Violation;
use DOMAttr;
use DOMDocument;
use DOMElement;
use Exception;

final class JUnitOutputFormatter implements OutputFormatterInterface
{
    private const DEFAULT_PATH = './junit-report.xml';

    public static function getName(): string
    {
        return 'junit';
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function finish(
        OutputResult $result,
        OutputInterface $output,
        OutputFormatterInput $outputFormatterInput,
    ): void {
        $xml = $this->createXml($result, $outputFormatterInput);

        $dumpXmlPath = $outputFormatterInput->outputPath ?? self::DEFAULT_PATH;
        file_put_contents($dumpXmlPath, $xml);
        $output->writeLineFormatted('<info>JUnit Report dumped to '.realpath($dumpXmlPath).'</info>');
    }

    /**
     * @throws Exception
     */
    private function createXml(OutputResult $result, OutputFormatterInput $outputFormatterInput): string
    {
        if (!class_exists(DOMDocument::class)) {
            throw new Exception('Unable to create xml file (php-xml needs to be installed)'); // @codeCoverageIgnore
        }

        $xmlDoc = new DOMDocument('1.0', 'UTF-8');
        $xmlDoc->formatOutput = true;

        $testSuite = $xmlDoc->createElement('testsuite');
        /** @throws void */
        $testSuite->appendChild(new DOMAttr('name', 'Deptrac'));
        /** @throws void */
        $testSuite->appendChild(new DOMAttr('tests', (string) count($result->rules)));
        /** @throws void */
        $testSuite->appendChild(new DOMAttr('failures', (string) count($result->violations())));
        /** @throws void */
        $testSuite->appendChild(new DOMAttr('skipped', (string) count($result->skippedViolations())));
        /** @throws void */
        $testSuite->appendChild(new DOMAttr('errors', (string) count($result->errors)));
        /** @throws void */
        $testSuite->appendChild(new DOMAttr('time', '0'));
        /** @throws void */
        $testSuite->appendChild(new DOMAttr('timestamp', $result->analysisComplete->format('Y-m-d\TH:i:s')));
        /** @throws void */
        $testSuite->appendChild(new DOMAttr('hostname', 'localhost'));
        /** @throws void */
        $testSuite->appendChild($xmlDoc->createElement('properties'));

        if ($result->hasErrors()) {
            /** @throws void */
            $testCase = $xmlDoc->createElement('testcase');
            /** @throws void */
            $testCase->appendChild(new DOMAttr('name', 'Analysis Errors'));
            /** @throws void */
            $testCase->appendChild(new DOMAttr('classname', 'N/A'));
            /** @throws void */
            $testCase->appendChild(new DOMAttr('time', '0'));

            foreach ($result->errors as $error) {
                /** @throws void */
                $errorElement = $xmlDoc->createElement('error');
                /** @throws void */
                $errorElement->appendChild(new DOMAttr('message', (string) $error));
                /** @throws void */
                $errorElement->appendChild(new DOMAttr('type', 'Analysis Error'));
                $testCase->appendChild($errorElement);
            }

            $testSuite->appendChild($testCase);
        }

        $this->addTestCases($result, $xmlDoc, $testSuite, $outputFormatterInput);

        /** @throws void */
        $testSuite->appendChild($xmlDoc->createElement('system-out'));
        /** @throws void */
        $testSuite->appendChild($xmlDoc->createElement('system-err'));

        $xmlDoc->appendChild($testSuite);

        return (string) $xmlDoc->saveXML();
    }

    private function addTestCases(OutputResult $result, DOMDocument $xmlDoc, DOMElement $testSuite, OutputFormatterInput $outputFormatterInput): void
    {
        /** @var array<string, array<RuleInterface>> $layers */
        $layers = [];
        foreach ($result->allRules() as $rule) {
            if ($rule instanceof CoveredRuleInterface) {
                $layers[$rule->getDependerLayer()][] = $rule;
            } elseif ($rule instanceof Uncovered) {
                $layers[$rule->layer][] = $rule;
            }
        }

        foreach ($layers as $layer => $rules) {
            foreach ($rules as $rule) {
                $className = $rule->getDependency()->getDepender()->toString();
                /** @throws void */
                $testCase = $xmlDoc->createElement('testcase');
                /** @throws void */
                $testCase->appendChild(new DOMAttr('name', $layer));
                /** @throws void */
                $testCase->appendChild(new DOMAttr('classname', $className));
                /** @throws void */
                $testCase->appendChild(new DOMAttr('time', '0'));

                if ($rule instanceof SkippedViolation && $outputFormatterInput->reportSkipped) {
                    $this->addSkipped($rule, $xmlDoc, $testCase);
                } elseif ($rule instanceof Violation) {
                    $this->addFailure($rule, $xmlDoc, $testCase);
                } elseif ($rule instanceof Uncovered && $outputFormatterInput->reportUncovered) {
                    $this->addWarning($rule, $xmlDoc, $testCase, $outputFormatterInput);
                }
                $testSuite->appendChild($testCase);
            }
        }
    }

    private function addFailure(Violation $violation, DOMDocument $xmlDoc, DOMElement $testCase): void
    {
        $dependency = $violation->getDependency();

        $message = sprintf(
            '%s:%d must not depend on %s (%s on %s)',
            $dependency->getDepender()->toString(),
            $dependency->getContext()->fileOccurrence->line,
            $dependency->getDependent()->toString(),
            $violation->getDependerLayer(),
            $violation->getDependentLayer()
        );

        /** @throws void */
        $error = $xmlDoc->createElement('failure');
        /** @throws void */
        $error->appendChild(new DOMAttr('message', $message));
        /** @throws void */
        $error->appendChild(new DOMAttr('type', 'Rule Violation'));

        $testCase->appendChild($error);
    }

    private function addSkipped(SkippedViolation $violation, DOMDocument $xmlDoc, DOMElement $testCase): void
    {
        $dependency = $violation->getDependency();

        $message = sprintf(
            '%s:%d must not depend on %s (%s on %s)',
            $dependency->getDepender()->toString(),
            $dependency->getContext()->fileOccurrence->line,
            $dependency->getDependent()->toString(),
            $violation->getDependerLayer(),
            $violation->getDependentLayer()
        );

        /** @throws void */
        $skipped = $xmlDoc->createElement('skipped', $message);
        $testCase->appendChild($skipped);
    }

    private function addWarning(Uncovered $rule, DOMDocument $xmlDoc, DOMElement $testCase, OutputFormatterInput $outputFormatterInput): void
    {
        $dependency = $rule->getDependency();

        $message = sprintf(
            '%s:%d has uncovered dependency on %s (%s)',
            $dependency->getDepender()->toString(),
            $dependency->getContext()->fileOccurrence->line,
            $dependency->getDependent()->toString(),
            $rule->layer
        );

        /** @throws void */
        $error = $xmlDoc->createElement($outputFormatterInput->failOnUncovered ? 'failure' : 'system-out');
        /** @throws void */
        $error->appendChild(new DOMAttr('message', $message));
        /** @throws void */
        $error->appendChild(new DOMAttr('type', 'Uncovered dependency'));

        $testCase->appendChild($error);
    }
}

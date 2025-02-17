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
use Deptrac\Deptrac\Contract\Result\CoveredRuleInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\RuleInterface;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Contract\Result\Violation;
/**
 * @internal
 */
final class JUnitOutputFormatter implements OutputFormatterInterface
{
    private const DEFAULT_PATH = './junit-report.xml';
    public static function getName() : string
    {
        return 'junit';
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
        $output->writeLineFormatted('<info>JUnit Report dumped to ' . \realpath($dumpXmlPath) . '</info>');
    }
    /**
     * @throws Exception
     */
    private function createXml(OutputResult $result) : string
    {
        if (!\class_exists(DOMDocument::class)) {
            throw new Exception('Unable to create xml file (php-xml needs to be installed)');
        }
        $xmlDoc = new DOMDocument('1.0', 'UTF-8');
        $xmlDoc->formatOutput = \true;
        $this->addTestSuites($result, $xmlDoc);
        return (string) $xmlDoc->saveXML();
    }
    private function addTestSuites(OutputResult $result, DOMDocument $xmlDoc) : void
    {
        /** @throws void */
        $testSuites = $xmlDoc->createElement('testsuites');
        $xmlDoc->appendChild($testSuites);
        if ($result->hasErrors()) {
            /** @throws void */
            $testSuite = $xmlDoc->createElement('testsuite');
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('id', '0'));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('package', ''));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('name', 'Unmatched skipped violations'));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('hostname', 'localhost'));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('tests', '0'));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('failures', '0'));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('skipped', '0'));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('errors', (string) \count($result->errors)));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('time', '0'));
            foreach ($result->errors as $message) {
                /** @throws void */
                $error = $xmlDoc->createElement('error');
                /** @throws void */
                $error->appendChild(new DOMAttr('message', (string) $message));
                /** @throws void */
                $error->appendChild(new DOMAttr('type', 'WARNING'));
                $testSuite->appendChild($error);
            }
            $testSuites->appendChild($testSuite);
        }
        $this->addTestSuite($result, $xmlDoc, $testSuites);
    }
    private function addTestSuite(OutputResult $result, DOMDocument $xmlDoc, DOMElement $testSuites) : void
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
        $layerIndex = 0;
        foreach ($layers as $layer => $rules) {
            $violationsByLayer = \array_filter($rules, static fn(RuleInterface $rule) => $rule instanceof Violation);
            $skippedViolationsByLayer = \array_filter($rules, static fn(RuleInterface $rule) => $rule instanceof SkippedViolation);
            $rulesByClassName = [];
            foreach ($rules as $rule) {
                $rulesByClassName[$rule->getDependency()->getDepender()->toString()][] = $rule;
            }
            /** @throws void */
            $testSuite = $xmlDoc->createElement('testsuite');
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('id', (string) ++$layerIndex));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('package', ''));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('name', $layer));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('hostname', 'localhost'));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('tests', (string) \count($rulesByClassName)));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('failures', (string) \count($violationsByLayer)));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('skipped', (string) \count($skippedViolationsByLayer)));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('errors', '0'));
            /** @throws void */
            $testSuite->appendChild(new DOMAttr('time', '0'));
            $testSuites->appendChild($testSuite);
            $this->addTestCase($layer, $rulesByClassName, $xmlDoc, $testSuite);
        }
    }
    /**
     * @param array<string, RuleInterface[]> $rulesByClassName
     */
    private function addTestCase(string $layer, array $rulesByClassName, DOMDocument $xmlDoc, DOMElement $testSuite) : void
    {
        foreach ($rulesByClassName as $className => $rules) {
            /** @throws void */
            $testCase = $xmlDoc->createElement('testcase');
            /** @throws void */
            $testCase->appendChild(new DOMAttr('name', $layer . ' - ' . $className));
            /** @throws void */
            $testCase->appendChild(new DOMAttr('classname', $className));
            /** @throws void */
            $testCase->appendChild(new DOMAttr('time', '0'));
            foreach ($rules as $rule) {
                if ($rule instanceof SkippedViolation) {
                    $this->addSkipped($xmlDoc, $testCase);
                } elseif ($rule instanceof Violation) {
                    $this->addFailure($rule, $xmlDoc, $testCase);
                } elseif ($rule instanceof Uncovered) {
                    $this->addWarning($rule, $xmlDoc, $testCase);
                }
            }
            $testSuite->appendChild($testCase);
        }
    }
    private function addFailure(Violation $violation, DOMDocument $xmlDoc, DOMElement $testCase) : void
    {
        $dependency = $violation->getDependency();
        $message = \sprintf('%s:%d must not depend on %s (%s on %s)', $dependency->getDepender()->toString(), $dependency->getContext()->fileOccurrence->line, $dependency->getDependent()->toString(), $violation->getDependerLayer(), $violation->getDependentLayer());
        /** @throws void */
        $error = $xmlDoc->createElement('failure');
        /** @throws void */
        $error->appendChild(new DOMAttr('message', $message));
        /** @throws void */
        $error->appendChild(new DOMAttr('type', 'WARNING'));
        $testCase->appendChild($error);
    }
    private function addSkipped(DOMDocument $xmlDoc, DOMElement $testCase) : void
    {
        /** @throws void */
        $skipped = $xmlDoc->createElement('skipped');
        $testCase->appendChild($skipped);
    }
    private function addWarning(Uncovered $rule, DOMDocument $xmlDoc, DOMElement $testCase) : void
    {
        $dependency = $rule->getDependency();
        $message = \sprintf('%s:%d has uncovered dependency on %s (%s)', $dependency->getDepender()->toString(), $dependency->getContext()->fileOccurrence->line, $dependency->getDependent()->toString(), $rule->layer);
        /** @throws void */
        $error = $xmlDoc->createElement('warning');
        /** @throws void */
        $error->appendChild(new DOMAttr('message', $message));
        /** @throws void */
        $error->appendChild(new DOMAttr('type', 'WARNING'));
        $testCase->appendChild($error);
    }
}

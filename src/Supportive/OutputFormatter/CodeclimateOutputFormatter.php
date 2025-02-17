<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use Exception;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\RuleInterface;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Contract\Result\Violation;
use Deptrac\Deptrac\Supportive\OutputFormatter\Configuration\ConfigurationCodeclimate;
use Deptrac\Deptrac\Supportive\OutputFormatter\Configuration\FormatterConfiguration;
use function json_encode;
use function json_last_error;
use function sprintf;
use const JSON_PRETTY_PRINT;
/**
 * @internal
 */
final class CodeclimateOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var array{severity?: array{failure?: string, skipped?: string, uncovered?: string}}
     */
    private readonly array $config;
    public function __construct(FormatterConfiguration $config)
    {
        /** @var array{severity?: array{failure?: string, skipped?: string, uncovered?: string}} $extractedConfig */
        $extractedConfig = $config->getConfigFor('codeclimate');
        $this->config = $extractedConfig;
    }
    public static function getName() : string
    {
        return 'codeclimate';
    }
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function finish(OutputResult $result, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        $formatterConfig = ConfigurationCodeclimate::fromArray($this->config);
        $violations = [];
        if ($outputFormatterInput->reportSkipped) {
            foreach ($result->allOf(SkippedViolation::class) as $rule) {
                $this->addSkipped($violations, $rule, $formatterConfig);
            }
        }
        if ($outputFormatterInput->reportUncovered) {
            foreach ($result->allOf(Uncovered::class) as $rule) {
                $this->addUncovered($violations, $rule, $formatterConfig);
            }
        }
        foreach ($result->allOf(Violation::class) as $rule) {
            $this->addFailure($violations, $rule, $formatterConfig);
        }
        $json = json_encode($violations, JSON_PRETTY_PRINT);
        if (\false === $json) {
            throw new Exception(sprintf('Unable to render codeclimate output. %s', $this->jsonLastError()));
        }
        $dumpJsonPath = $outputFormatterInput->outputPath;
        if (null !== $dumpJsonPath) {
            \file_put_contents($dumpJsonPath, $json);
            $output->writeLineFormatted('<info>Codeclimate Report dumped to ' . \realpath($dumpJsonPath) . '</info>');
            return;
        }
        $output->writeRaw($json);
    }
    /**
     * @param array<array{type: string, check_name: string, fingerprint: string, description: string, categories: array<string>, severity: string, location: array{path: string, lines: array{begin: int}}}> $violationsArray
     */
    private function addFailure(array &$violationsArray, Violation $violation, ConfigurationCodeclimate $config) : void
    {
        $violationsArray[] = $this->buildRuleArray($violation, $this->getFailureMessage($violation), $config->getSeverity('failure') ?? 'major');
    }
    private function getFailureMessage(Violation $violation) : string
    {
        $dependency = $violation->getDependency();
        return sprintf('%s must not depend on %s (%s on %s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $violation->getDependerLayer(), $violation->getDependentLayer());
    }
    /**
     * @param array<array{type: string, check_name: string, fingerprint: string, description: string, categories: array<string>, severity: string, location: array{path: string, lines: array{begin: int}}}> $violationsArray
     */
    private function addSkipped(array &$violationsArray, SkippedViolation $violation, ConfigurationCodeclimate $config) : void
    {
        $violationsArray[] = $this->buildRuleArray($violation, $this->getWarningMessage($violation), $config->getSeverity('skipped') ?? 'minor');
    }
    private function getWarningMessage(SkippedViolation $violation) : string
    {
        $dependency = $violation->getDependency();
        return sprintf('%s should not depend on %s (%s on %s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $violation->getDependerLayer(), $violation->getDependentLayer());
    }
    /**
     * @param array<array{type: string, check_name: string, fingerprint: string, description: string, categories: array<string>, severity: string, location: array{path: string, lines: array{begin: int}}}> $violationsArray
     */
    private function addUncovered(array &$violationsArray, Uncovered $violation, ConfigurationCodeclimate $config) : void
    {
        $violationsArray[] = $this->buildRuleArray($violation, $this->getUncoveredMessage($violation), $config->getSeverity('uncovered') ?? 'info');
    }
    private function getUncoveredMessage(Uncovered $violation) : string
    {
        $dependency = $violation->getDependency();
        return sprintf('%s has uncovered dependency on %s (%s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $violation->layer);
    }
    private function jsonLastError() : string
    {
        return match (json_last_error()) {
            \JSON_ERROR_NONE => 'No errors',
            \JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            \JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            \JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            \JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            \JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            default => 'Unknown error',
        };
    }
    /**
     * @return array{type: string, check_name: string, fingerprint: string, description: string, categories: array<string>, severity: string, location: array{path: string, lines: array{begin: int}}}
     */
    private function buildRuleArray(RuleInterface $rule, string $message, string $severity) : array
    {
        return ['type' => 'issue', 'check_name' => 'Dependency violation', 'fingerprint' => $this->buildFingerprint($rule), 'description' => $message, 'categories' => ['Style', 'Complexity'], 'severity' => $severity, 'location' => ['path' => $rule->getDependency()->getContext()->fileOccurrence->filepath, 'lines' => ['begin' => $rule->getDependency()->getContext()->fileOccurrence->line]]];
    }
    private function buildFingerprint(RuleInterface $rule) : string
    {
        return \sha1(\implode(',', [$rule::class, $rule->getDependency()->getDepender()->toString(), $rule->getDependency()->getDependent()->toString(), $rule->getDependency()->getContext()->fileOccurrence->filepath, $rule->getDependency()->getContext()->fileOccurrence->line]));
    }
}

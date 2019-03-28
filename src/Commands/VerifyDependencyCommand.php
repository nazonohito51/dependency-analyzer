<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\Detector\RuleViolationDetector;
use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRuleFactory;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Exceptions\InvalidCommandArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyDependencyCommand extends AnalyzeDependencyCommand
{
    protected $ruleDefinition;

    protected function getCommandName(): string
    {
        return 'verify';
    }

    protected function getCommandDescription(): string
    {
        return 'verify dependency map by rule';
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('rule', 'r', InputOption::VALUE_REQUIRED, 'Rule file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $ruleFile = $input->getOption('rule');
        if (!is_file($ruleFile)) {
            throw new InvalidCommandArgumentException(sprintf('rule is not file "%s".', $ruleFile));
        }
        $this->ruleDefinition = require_once $ruleFile;
        if (!is_array($this->ruleDefinition)) {
            throw new InvalidCommandArgumentException(sprintf('rule is invalid file "%s".', $ruleFile));
        }
    }

    protected function inspectDependencyGraph(DependencyGraph $graph, OutputInterface $output): int
    {
        $detector = new RuleViolationDetector((new DependencyRuleFactory())->create(array_merge(
            $this->ruleDefinition,
            $this->createRuleDefinitionFromPhpDoc($graph)
        )));
        $responses = $detector->inspect($graph);

        $errorCount = 0;
        foreach ($responses as $respons) {
            if ($respons->count() > 0) {
                $errorCount += $respons->count();
                $output->writeln($respons->getRuleName());
                foreach ($respons->getViolations() as $violation) {
                    $output->writeln("| {$violation['dependerComponent']} | {$violation['depender']} | -> | {$violation['dependeeComponent']} | {$violation['dependee']} |");
                }
            }
        }

        return $errorCount > 0 ? 1 : 0;
    }

    protected function createRuleDefinitionFromPhpDoc(DependencyGraph $graph): array
    {
        $ruleDefinitions = [];
        foreach ($graph->getClassesHaveOnlyUsedTag() as $class => $classesInPhpDoc) {
            // TODO: Is '\\' needed?
            $targetComponent = [
                'define' => ['\\' . $class],
                'depender' => $classesInPhpDoc
            ];

            $otherComponent = [
                'define' => array_merge(['\\'], array_map(function (string $className) {
                    return '!' . $className;
                }, $classesInPhpDoc)),
            ];

            $ruleDefinitions['phpdoc in ' . $class] = [
                'phpdoc' => $targetComponent,
                'other' => $otherComponent
            ];
        }

        return $ruleDefinitions;
    }
}

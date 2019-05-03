<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\Inspector\RuleViolationDetector;
use DependencyAnalyzer\Inspector\RuleViolationDetector\DependencyRule;
use DependencyAnalyzer\Inspector\RuleViolationDetector\DependencyRuleFactory;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Exceptions\InvalidCommandArgumentException;
use LucidFrame\Console\ConsoleTable;
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
        if (is_null($ruleFile)) {
            $this->ruleDefinition = [];
        } else {
            if (!is_file($ruleFile)) {
                throw new InvalidCommandArgumentException(sprintf('rule is not file "%s".', $ruleFile));
            }
            $this->ruleDefinition = require_once $ruleFile;
            if (!is_array($this->ruleDefinition)) {
                throw new InvalidCommandArgumentException(sprintf('rule is invalid file "%s".', $ruleFile));
            }
        }
    }

    protected function inspectDependencyGraph(DependencyGraph $graph, OutputInterface $output): int
    {
        $rules = (new DependencyRuleFactory())->create(array_merge(
            $this->ruleDefinition,
            $this->createRuleDefinitionFromPhpDoc($graph)
        ));
        $this->debugRules($rules, $output);
        $detector = new RuleViolationDetector($rules);
        $responses = $detector->inspect($graph);

        $errorCount = 0;
        foreach ($responses as $response) {
            if ($response->count() > 0) {
                $table = (new ConsoleTable())
                    ->addHeader('depender')
                    ->addHeader('component')
                    ->addHeader('')
                    ->addHeader('dependee')
                    ->addHeader('component');

                $errorCount += $response->count();
                $output->writeln('');
                $output->writeln($response->getRuleName());
                foreach ($response->getViolations() as $violation) {
                    $table->addRow([
                        $violation['depender'],
                        $violation['dependerComponent'],
                        '->',
                        $violation['dependee'],
                        $violation['dependeeComponent']
                    ]);
                }

                $output->write($table->getTable());
            }
        }

        if ($errorCount === 0) {
            $output->write('rule violation is not found.');
        }

        return $errorCount > 0 ? 1 : 0;
    }

    protected function createRuleDefinitionFromPhpDoc(DependencyGraph $graph): array
    {
        $ruleDefinitions = [];

        foreach ($graph->getClasses() as $class) {
            if (!empty($depsInternals = $class->getDepsInternalTag())) {
                $definePattern = [];
                $extraPatterns = [];
                foreach ($depsInternals as $depsInternal) {
                    $calleeName = $depsInternal->getFqsen()->toString();
                    if (!in_array($calleeName, $definePattern)) {
                        $definePattern[] = $calleeName;
                    }
                    $extraPatterns[$calleeName] = $depsInternal->getTargetsAsString();
                }
                $ruleDefinitions['phpdoc in ' . $class->getName()] = [
                    'phpdoc' => [
                        'define' => $definePattern,
                        'depender' => ['!\\'],
                        'extra' => $extraPatterns
                    ],
                    'other' => [
                        'define' => ['!' . $class->getName()],
                    ]
                ];
            }
        }

        return $ruleDefinitions;
    }

    /**
     * @param DependencyRule[] $rules
     * @param OutputInterface $output
     */
    protected function debugRules(array $rules, OutputInterface $output): void
    {
        if ($output->isVerbose()) {
            $output->writeln('');
            $output->writeln('Defined rules:');
            foreach ($rules as $rule) {
                $output->writeln(var_export($rule->toArray(), true));
            }
        }
    }
}

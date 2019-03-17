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
    protected $ruleFile;

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
        $this->ruleFile = $input->getOption('rule');
    }

    protected function inspectDependencyGraph(DependencyGraph $graph): int
    {
        if (!is_file($this->ruleFile)) {
            throw new InvalidCommandArgumentException(sprintf('rule is not file "%s".', $this->ruleFile));
        }
        $ruleDefinition = require_once $this->ruleFile;
        if (!is_array($ruleDefinition)) {
            throw new InvalidCommandArgumentException(sprintf('rule is invalid file "%s".', $this->ruleFile));
        }

        $detector = new RuleViolationDetector((new DependencyRuleFactory())->create($ruleDefinition));
        $result = $detector->inspect($graph);
        var_dump($result);

        return count($result) > 0 ? 1 : 0;
    }
}

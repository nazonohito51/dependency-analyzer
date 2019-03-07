<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\Detector\RuleViolationDetector;
use DependencyAnalyzer\Detector\RuleViolationDetector\RuleFactory;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Exceptions\InvalidCommandArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyDependencyCommand extends AnalyzeDependencyCommand
{
    protected const NAME = 'verify';
    protected const DESCRIPTION = 'verify dependency map by rule';

    protected $ruleFile;

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

    protected function inspectGraph(DependencyGraph $graph): int
    {
        if (!is_file($this->ruleFile)) {
            throw new InvalidCommandArgumentException(sprintf('rule is not file "%s".', $this->ruleFile));
        }
        $ruleDefinition = require_once $this->ruleFile;
        if (!is_array($ruleDefinition)) {
            throw new InvalidCommandArgumentException(sprintf('rule is invalid file "%s".', $this->ruleFile));
        }

        $detector = new RuleViolationDetector((new RuleFactory($ruleDefinition))->create());
        $result = $detector->inspect($graph);
        var_dump($result);

        return count($result) > 0 ? 1 : 0;
    }
}

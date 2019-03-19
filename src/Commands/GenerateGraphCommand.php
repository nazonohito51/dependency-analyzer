<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\Formatter\UmlFormatter;
use DependencyAnalyzer\Exceptions\InvalidCommandArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateGraphCommand extends AnalyzeDependencyCommand
{
    protected $output;
    protected $ruleFile;

    protected function getCommandName(): string
    {
        return 'graph';
    }

    protected function getCommandDescription(): string
    {
        return 'generate dependency map graph';
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'output path of graph image file');
        $this->addOption('rule', 'r', InputOption::VALUE_REQUIRED, 'Rule file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->output = $input->getOption('output');
        $this->ruleFile = $input->getOption('rule');
    }

    protected function inspectDependencyGraph(DependencyGraph $graph): int
    {
        if ($this->ruleFile) {
            if (!is_file($this->ruleFile)) {
                throw new InvalidCommandArgumentException(sprintf('rule is not file "%s".', $this->ruleFile));
            }
            $ruleDefinition = require_once $this->ruleFile;
            if (!is_array($ruleDefinition)) {
                throw new InvalidCommandArgumentException(sprintf('rule is invalid file "%s".', $this->ruleFile));
            }
        } else {
            $ruleDefinition = [];
        }

        $formatter = new UmlFormatter($graph, $ruleDefinition);

        $outputFile = new \SplFileObject($this->output, 'w');
        $outputFile->fwrite($formatter->format());

        return 0;
    }
}

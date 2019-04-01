<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Inspector\GraphFormatter\UmlFormatter;
use DependencyAnalyzer\Exceptions\InvalidCommandArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateGraphCommand extends AnalyzeDependencyCommand
{
    protected $output;
    protected $ruleDefinition = [];

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

        if ($ruleFile = $input->getOption('rule')) {
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
        $formatter = new UmlFormatter($graph, $this->ruleDefinition);

        $outputFile = new \SplFileObject($this->output, 'w');
        $outputFile->fwrite($formatter->format());

        $output->writeln("generated graph: {$this->output}");

        return 0;
    }
}

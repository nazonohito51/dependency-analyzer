<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\Formatter\UmlFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateGraphCommand extends AnalyzeDependencyCommand
{
    protected const NAME = 'graph';
    protected const DESCRIPTION = 'generate dependency map graph';

    protected $output;

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'output path of graph image file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->output = $input->getOption('output');
    }

    protected function inspectGraph(DependencyGraph $graph): int
    {
        $formatter = new UmlFormatter($graph);

        $outputFile = new \SplFileObject($this->output, 'w');
        $outputFile->fwrite($formatter->format());

        return 0;
    }
}

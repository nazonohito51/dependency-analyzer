<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\Inspector\CycleDetector;
use DependencyAnalyzer\DependencyGraph;
use LucidFrame\Console\ConsoleTable;
use Symfony\Component\Console\Output\OutputInterface;

class CycleDetectCommand extends AnalyzeDependencyCommand
{
    protected function getCommandName(): string
    {
        return 'detect-cycle';
    }

    protected function getCommandDescription(): string
    {
        return 'detect cycle dependency in dependency map';
    }

    protected function inspectDependencyGraph(DependencyGraph $graph, OutputInterface $output): int
    {
        $response = (new CycleDetector())->inspect($graph);

        $output->writeln("{$response->count()} cycles detected.");
        $output->writeln('');

        foreach ($response->getCycles() as $cycle) {
            $table = (new ConsoleTable())
                ->addHeader('class')
                ->addHeader('');
            foreach ($cycle as $index => $class) {
                $haveNextClass = (bool)($index < (count($cycle) - 1));
                $table->addRow([$class, $haveNextClass ? '->' : '']);
            }

            $output->write($table->getTable());
            $output->writeln('');
        }

        return count($response) > 0 ? 1 : 0;
    }
}

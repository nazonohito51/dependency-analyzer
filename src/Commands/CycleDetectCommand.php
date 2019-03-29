<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\Detector\CycleDetector;
use DependencyAnalyzer\DependencyGraph;
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
            foreach ($cycle as $index => $class) {
                if ($index < count($cycle) - 1) {
                    $output->writeln("| {$class} | -> |");
                } else {
                    $output->writeln("| {$class} | |");
                }
            }

            $output->writeln('');
        }

        return count($response) > 0 ? 1 : 0;
    }
}

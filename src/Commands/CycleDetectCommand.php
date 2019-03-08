<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\Detector\CycleDetector;
use DependencyAnalyzer\DependencyGraph;

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

    protected function inspectDependencyGraph(DependencyGraph $graph): int
    {
        $result = (new CycleDetector())->inspect($graph);
        var_dump($result);

        return count($result) > 0 ? 1 : 0;
    }
}

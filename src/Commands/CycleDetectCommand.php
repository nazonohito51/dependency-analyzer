<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\Detector\CycleDetector;
use DependencyAnalyzer\DirectedGraph;

class CycleDetectCommand extends AnalyzeDependencyCommand
{
    protected const NAME = 'detect-cycle';
    protected const DESCRIPTION = 'detect cycle dependency in dependency map';

    protected function inspectGraph(DirectedGraph $graph): int
    {
        $result = (new CycleDetector())->inspect($graph);
        var_dump($result);

        return count($result) > 0 ? 1 : 0;
    }
}

<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\Path;
use DependencyAnalyzer\Exceptions\LogicException;
use DependencyAnalyzer\Responses\CycleDetecterResponse;

class CycleDetector
{
    /**
     * @var Path[]
     */
    protected $cycles = [];

    public function inspect(DependencyGraph $graph): CycleDetecterResponse
    {
        $subGraphs = $this->collectConnectedSubGraphs($graph);

        foreach ($subGraphs as $subGraph) {
            $subGraph->walkOnPath([$this, 'checkCycle']);
        }

        $response = new CycleDetecterResponse();
        foreach ($this->cycles as $cycle) {
            $response->addCycle($cycle->toArray());
        }

        return $response;
    }

    public function checkCycle(Path $path): void
    {
        if ($path->isSimpleCycle()) {
            $this->addCycle($path);
        }
    }

    protected function addCycle(Path $path): void
    {
        foreach ($this->cycles as $cycle) {
            if ($cycle->isEqual($path)) {
                return;
            }
        }

        $this->cycles[] = $path;
    }

    /**
     * @param DependencyGraph $graph
     * @return DependencyGraph[]
     */
    protected function collectConnectedSubGraphs(DependencyGraph $graph): array
    {
        $subGraphs = [];

        $visited = [];
        foreach ($graph->getClasses()->getMap() as $id => $vertex) {
            if (!isset($visited[$id])) {
                $subGraphs[] = $subGraph = $graph->getConnectedSubGraphsStartFrom($vertex);

                foreach ($subGraph->getClasses() as $subGraphVertex) {
                    $visited[$subGraphVertex->getId()] = true;
                }
            }
        }

        if ($graph->count() !== count($visited)) {
            throw new LogicException();
        }
        return $subGraphs;
    }
}

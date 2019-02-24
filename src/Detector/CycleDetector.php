<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector;

use DependencyAnalyzer\DirectedGraph;
use DependencyAnalyzer\DirectedGraph\Path;
use DependencyAnalyzer\Exceptions\LogicException;

class CycleDetector
{
    /**
     * @var Path[]
     */
    protected $cycles = [];

    public function inspect(DirectedGraph $graph)
    {
        $subGraphs = $this->collectConnectedSubGraphs($graph);

        foreach ($subGraphs as $subGraph) {
            $edges = $subGraph->getEdges();
            foreach ($edges as $edge) {
                $subGraph->walkOnPath($edge, [$this, 'checkCycle']);
            }
        }

        return array_map(function (Path $path) {
            return $path->getIds();
        }, $this->cycles);
    }

    public function checkCycle(Path $path)
    {
        if ($path->isSimpleCycle()) {
            $this->addCycle($path);
        }
    }

    protected function addCycle(Path $path)
    {
        foreach ($this->cycles as $cycle) {
            if ($cycle->isEqual($path)) {
                return;
            }
        }

        $this->cycles[] = $path;
    }

    /**
     * @param DirectedGraph $graph
     * @return DirectedGraph[]
     */
    protected function collectConnectedSubGraphs(DirectedGraph $graph): array
    {
        $subGraphs = [];

        $visited = [];
        foreach ($graph->getVertices()->getMap() as $id => $vertex) {
            if (!isset($visited[$id])) {
                $subGraphs[] = $subGraph = $graph->getConnectedSubGraphsStartFrom($vertex);

                foreach ($subGraph->getVertices() as $subGraphVertex) {
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

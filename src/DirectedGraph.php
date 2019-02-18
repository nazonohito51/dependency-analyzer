<?php
namespace DependencyAnalyzer;

use Fhaculty\Graph\Graph;

class DirectedGraph implements \Countable
{
    /**
     * @var Graph
     */
    private $graph;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    public function toArray()
    {
        $ret = [];

        foreach ($this->graph->getVertices() as $vertex) {
            $ret[$vertex->getId()] = [];

            foreach ($vertex->getVerticesEdgeTo() as $edgeTo) {
                $ret[$vertex->getId()][] = $edgeTo->getId();
            }
        }

        return $ret;
    }

    public function count()
    {
        return count($this->graph->getVertices());
    }
}

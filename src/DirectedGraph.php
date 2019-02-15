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

    public function count()
    {
        return count($this->graph->getVertices());
    }
}

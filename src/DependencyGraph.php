<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraph\Path;
use DependencyAnalyzer\Exceptions\InvalidEdgeOnDependencyGraphException;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Vertex;

class DependencyGraph implements \Countable
{
    /**
     * @var Graph
     */
    private $graph;

    public function __construct(Graph $graph)
    {
        foreach ($graph->getEdges() as $edge) {
            if (!$edge instanceof Directed) {
                throw new InvalidEdgeOnDependencyGraphException($edge);
            }
        }
        $this->graph = $graph;
    }

    /**
     * @param array $dependencies  ex: [$depender => [$dependee1, $dependee2]]
     * @return DependencyGraph
     */
    public static function createFromArray(array $dependencies): self
    {
        $graph = new Graph();
        $vertices = [];

        foreach ($dependencies as $depender => $dependees) {
            if (!isset($vertices[$depender])) {
                $vertices[$depender] = $graph->createVertex($depender);
            }

            foreach ($dependees as $dependee) {
                if (!isset($vertices[$dependee])) {
                    $vertices[$dependee] = $graph->createVertex($dependee);
                }
            }
        }

        foreach ($vertices as $vertex) {
            if (isset($dependencies[$vertex->getId()])) {
                foreach ($dependencies[$vertex->getId()] as $dependency) {
                    $vertex->createEdgeTo($vertices[$dependency]);
                };
            }
        }

        return new self($graph);
    }

    public function getVertices()
    {
        return $this->graph->getVertices();
    }

    public function getEdges()
    {
        return $this->graph->getEdges();
    }

    public function getConnectedSubGraphsStartFrom(Vertex $vertex)
    {
        $vertices = $this->collectConnectedVertices($vertex);
        return new self($this->graph->createGraphCloneVertices($vertices));
    }

    protected function collectConnectedVertices(Vertex $start)
    {
        $visited = [];

        /**
         * @var Vertex[] $queue
         */
        $queue = [$start];
        // Breadth first search
        do {
            $target = array_shift($queue);
            $visited[$target->getId()]= $target;

            foreach ($target->getVerticesEdgeTo()->getMap() as $id => $vertexTo) {
                if (!isset($visited[$id])) {
                    $queue[] = $vertexTo;
                }
            }
        } while ($queue);

        return new Vertices(array_values($visited));
    }

    public function walkOnPath(callable $carry)
    {
        foreach ($this->graph->getEdges() as $edge) {
            $this->walkThroughEdge($edge, new Path(), $carry);
        }
    }

    protected function walkThroughEdge(Directed $edge, Path $path, callable $carry)
    {
        $path = $path->addEdge($edge);
        $carry($path);

        if (!$path->haveCycle()) {
            $edgesOut = $edge->getVertexEnd()->getEdgesOut();
            foreach ($edgesOut as $edgeOut) {
                $this->walkThroughEdge($edgeOut, $path, $carry);
            }
        }
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

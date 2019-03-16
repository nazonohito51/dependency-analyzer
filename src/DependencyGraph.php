<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraph\ClassLikeAggregate;
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
     * @param ClassLikeAggregate $dependencies
     * @return DependencyGraph
     */
    public static function createFromClassLikeAggregate(ClassLikeAggregate $dependencies): self
    {
        $graph = new Graph();

        foreach ($dependencies->getClassLikes() as $classLike) {
            if (!$graph->hasVertex($classLike->getName())) {
                $vertex = $graph->createVertex($classLike->getName());
                $vertex->setAttribute('reflection', $classLike->getReflection());
            }
            $depender = $graph->getVertex($classLike->getName());

            foreach ($classLike->getDependees() as $dependee) {
                if (!$graph->hasVertex($dependee->getName())) {
                    $vertex = $graph->createVertex($dependee->getName());
                    $vertex->setAttribute('reflection', $dependee);
                }
                $dependee = $graph->getVertex($dependee->getName());

                $depender->createEdgeTo($dependee);
            }
        }

        return new self($graph);
    }

    public function getClasses()
    {
        return $this->graph->getVertices();
    }

    public function getDependencyArrows()
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

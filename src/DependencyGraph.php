<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraph\DependencyTypes\Base as DependencyType;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;
use DependencyAnalyzer\DependencyGraph\Path;
use DependencyAnalyzer\Exceptions\InvalidEdgeOnDependencyGraphException;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Vertex;

class DependencyGraph implements \Countable
{
    const DEPENDENCY_TYPE_KEY = 'dependency_types';

    const TYPE_SOME_DEPENDENCY = 'some_dependency';
    const TYPE_NEW = 'new';
    const TYPE_METHOD_CALL = 'method_call';
    const TYPE_PROPERTY_FETCH = 'property_fetch';
    const TYPE_CONSTANT_FETCH = 'constant_fetch';
    const TYPE_EXTENDS = 'extends';
    const TYPE_IMPLEMENTS = 'implements';
    const TYPE_USE_TRAIT = 'use_trait';

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

    public function getGraph()
    {
        return $this->graph;
    }

    public function getClasses()
    {
        return $this->graph->getVertices();
    }

    public function getDependencyArrows()
    {
        return $this->graph->getEdges();
    }

    public function groupByPattern(string $name, StructuralElementPatternMatcher $pattern)
    {
        $graph = new Graph();
        $graph->createVertex($name);
        foreach ($this->getClasses() as $class) {
            /** @var Vertex $class */
            if (!$pattern->isMatch($class->getId())) {
                $graph->createVertex($class->getId());
            }
        }

        foreach ($this->getDependencyArrows() as $dependencyArrow) {
            /** @var Directed $dependencyArrow */
            $start = $pattern->isMatch($dependencyArrow->getVertexStart()->getId()) ? $name : $dependencyArrow->getVertexStart()->getId();
            $end = $pattern->isMatch($dependencyArrow->getVertexEnd()->getId()) ? $name : $dependencyArrow->getVertexEnd()->getId();

            if ($start !== $end && !$graph->getVertex($start)->hasEdgeTo($graph->getVertex($end))) {
                $graph->getVertex($start)->createEdgeTo($graph->getVertex($end));
            }
        }

        return new self($graph);
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

    public function getClassesHaveOnlyUsedTag(): array
    {
        $classes = [];
        foreach ($this->getClasses() as $class) {
            /** @var Vertex $class */
            if (!empty($classNames = $class->getAttribute(ExtraPhpDocTagResolver::ONLY_USED_BY_TAGS))) {
                $classes[$class->getId()] = $classNames;
            }
        }

        return $classes;
    }

    public function toArray()
    {
        $ret = [];

        foreach ($this->graph->getVertices() as $vertex) {
            $ret[$vertex->getId()] = [];

            foreach ($vertex->getEdgesOut() as $edge) {
                /** @var Directed $edge */
                $types = $edge->getAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY) ?? [];

                $ret[$vertex->getId()][$edge->getVertexEnd()->getId()] = array_map(function (DependencyType $type) {
                    return $type->toString();
                }, $types);
            }
        }

        return $ret;
    }

    public function count()
    {
        return count($this->graph->getVertices());
    }
}

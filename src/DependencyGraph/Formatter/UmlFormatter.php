<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\Formatter;

class UmlFormatter
{
    /**
     * @var \DependencyAnalyzer\DependencyGraph
     */
    private $graph;

    public function __construct(\DependencyAnalyzer\DependencyGraph $graph)
    {
        $this->graph = $graph;
    }

    public function format()
    {
        $output = '@startuml' . PHP_EOL;

        foreach ($this->graph->getDependencyArrows() as $edge) {
            $depender = $edge->getVertexStart();
            $dependee = $edge->getVertexEnd();
            $output .= "{$depender->getId()} --> {$dependee->getId()}" . PHP_EOL;
        }

        $output .= '@enduml';

        return $output;
    }
}

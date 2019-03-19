<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\Formatter;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Detector\RuleViolationDetector\Component;
use DependencyAnalyzer\Patterns\QualifiedNamePattern;

class UmlFormatter
{
    /**
     * @var DependencyGraph
     */
    protected $graph;

    /**
     * @var array
     */
    protected $ruleDefinition;

    /**
     * @var Component[]
     */
    protected $components = [];

    public function __construct(DependencyGraph $graph, array $ruleDefinition = [])
    {
        $this->graph = $graph;
        $this->ruleDefinition = $ruleDefinition;

        foreach ($ruleDefinition as $componentName => $componentDefinition) {
            $this->components[] = new Component($componentName, new QualifiedNamePattern($componentDefinition));
        }
    }

    public function format()
    {
        $output = '@startuml' . PHP_EOL;

        foreach ($this->getGroupedClasses() as $componentName => $classes) {
            if ($componentName !== '') {
                $output .= "namespace {$componentName} {" . PHP_EOL;
            }

            foreach ($classes as $class) {
                $output .= "class {$class} {" . PHP_EOL;
                $output .= '}' . PHP_EOL;
            }

            if ($componentName !== '') {
                $output .= '}' . PHP_EOL;
            }
        }

        foreach ($this->graph->getDependencyArrows() as $edge) {
            $depender = $edge->getVertexStart();
            $dependee = $edge->getVertexEnd();
            $output .= "{$depender->getId()} --> {$dependee->getId()}" . PHP_EOL;
        }

        $output .= '@enduml';

        return $output;
    }

    protected function getGroupedClasses(): array
    {
        $classNames = [];
        foreach ($this->graph->getClasses() as $class) {
            $key = $this->getBelongToComponent($class->getId());
            $classNames[$key][] = $class->getId();
//            $classNames[] = $class->getId();
        }
//        sort($classNames);

        return $classNames;
    }

    protected function getBelongToComponent(string $className): string
    {
        foreach ($this->components as $component) {
            if ($component->isBelongedTo($className)) {
                return $component->getName();
            }
        }

        return '';
    }
}

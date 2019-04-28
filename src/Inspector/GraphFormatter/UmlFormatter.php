<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Inspector\GraphFormatter;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Inspector\RuleViolationDetector\Component;
use DependencyAnalyzer\DependencyGraph\ClassNameMatcher;

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

    protected $groupedClasses = [];

    /**
     * @var ClassNameMatcher
     */
    protected $excludeDefinition;

    /**
     * @param DependencyGraph $graph
     * @param Component[] $components
     */
    public function __construct(DependencyGraph $graph, array $components = [])
    {
        $this->graph = $this->createFoldedGraph($graph, $components);
        $this->components = $components;
//        $this->ruleDefinition = $ruleDefinition;

//        if (isset($ruleDefinition['namespace'])) {
//            foreach ($ruleDefinition['namespace'] as $componentName => $componentDefinition) {
//                $this->components[] = new Component($componentName, new ClassNameMatcher($componentDefinition));
//            }
//        }
//        if (isset($ruleDefinition['exclude'])) {
//            $this->excludeDefinition = new ClassNameMatcher($ruleDefinition['exclude']);
//        }
//        if (isset($ruleDefinition['group'])) {
//            foreach ($ruleDefinition['group'] as $groupName => $groupDefinition) {
//                $this->graph = $this->graph->groupByPattern($groupName, new ClassNameMatcher($groupDefinition));
//            }
//        }

        $this->groupedClasses = $this->getGroupedClasses($this->graph, $this->components);
    }

    public function format()
    {
        $output = '@startuml' . PHP_EOL;

        foreach ($this->groupedClasses as $componentName => $classNames) {
            $component = $this->getComponentByName($componentName);

            if (!is_null($component) && $component->getAttribute('namespace')) {
                $output .= "namespace {$component->getName()} {" . PHP_EOL;
            }

            foreach ($classNames as $className) {
                $output .= "class {$className} {" . PHP_EOL;
                $output .= '}' . PHP_EOL;
            }

            if (!is_null($component) && $component->getAttribute('namespace')) {
                $output .= '}' . PHP_EOL;
            }
        }

//        foreach ($this->components as $component) {
//            if ($component->getAttribute('namespace')) {
//                $output .= "namespace {$component->getName()} {" . PHP_EOL;
//            }
//
//            foreach ($this->graph->getClasses() as $class) {
//                if ($component->isBelongedTo($class->getId())) {
//                    if (!$this->isExcludeClass($class->getId())) {
//                        $output .= "class {$class->getId()} {" . PHP_EOL;
//                        $output .= '}' . PHP_EOL;
//                    }
//                }
//            }
//
//            if ($component->getAttribute('namespace')) {
//                $output .= '}' . PHP_EOL;
//            }
//        }
//
//        foreach ($this->graph->getClasses() as $class) {
//            if ($this->isExcludeClass($class->getId())) {
//                continue;
//            }
//
//            foreach ($this->components as $component) {
//                if ($component->getAttribute('namespace') && $component->isBelongedTo($class->getId())) {
//                    continue 2;
//                }
//            }
//
//            $output .= "class {$class->getId()} {" . PHP_EOL;
//            $output .= '}' . PHP_EOL;
//        }




//        foreach ($this->groupedClasses as $componentName => $classes) {
//            if ($componentName !== '') {
//                $output .= "namespace {$componentName} {" . PHP_EOL;
//            }
//
//            foreach ($classes as $class) {
//                if (!$this->isExcludeClass($class)) {
//                    $output .= "class {$class} {" . PHP_EOL;
//                    $output .= '}' . PHP_EOL;
//                }
//            }
//
//            if ($componentName !== '') {
//                $output .= '}' . PHP_EOL;
//            }
//        }

        foreach ($this->graph->getDependencyArrows() as $edge) {
            $depender = $edge->getVertexStart();
            $dependee = $edge->getVertexEnd();

            if ($this->isExcludeClass($depender->getId()) || $this->isExcludeClass($dependee->getId())) {
                continue;
            }
            $output .= "{$this->searchGroupedClasses($depender->getId())} --> {$this->searchGroupedClasses($dependee->getId())}" . PHP_EOL;
        }

        $output .= '@enduml';

        return $output;
    }

    protected function isExcludeClass(string $className)
    {
        if ($this->excludeDefinition) {
            return $this->excludeDefinition->isMatch($className);
        }

        return false;
    }

    /**
     * @param DependencyGraph $graph
     * @param Component[] $components
     * @return DependencyGraph
     */
    protected function createFoldedGraph(DependencyGraph $graph, array $components): DependencyGraph
    {
        foreach ($components as $component) {
            if ($component->getAttribute('folding')) {
                $graph = $graph->groupByPattern($component->getName(), $component->getDefineMatcher());
            }
        }

        return $graph;
    }

    /**
     * @param DependencyGraph $graph
     * @param Component[] $components
     * @return array
     */
    protected function getGroupedClasses(DependencyGraph $graph, array $components): array
    {
        $classNames = [];

        foreach ($graph->getClasses() as $class) {
            $key = $this->getBelongToComponent($class->getId(), $components);
            $classNames[$key][] = $class->getId();
//            $classNames[] = $class->getId();
        }
//        sort($classNames);

        return $classNames;
    }

    protected function getBelongToComponent(string $className, array $components): string
    {
        foreach ($components as $component) {
            if ($component->isBelongedTo($className)) {
                return $component->getName();
            }
        }

        return '';
    }

    protected function searchGroupedClasses(string $needle)
    {
        foreach ($this->groupedClasses as $componentName => $classes) {
            if ($componentName === '' || !$this->getComponentByName($componentName)->getAttribute('namespace')) {
                continue;
            }

            foreach ($classes as $class) {
                if ($class === $needle) {
                    if ($this->getComponentByName($componentName)->getAttribute('folding')) {
                        return $this->getComponentByName($componentName)->getName();
                    } else {
                        return "{$componentName}.$needle";
                    }
                }
            }
        }

        return $needle;
    }

    protected function getComponentByName(string $componentName): ?Component
    {
        foreach ($this->components as $component) {
            if ($component->getName() === $componentName) {
                return $component;
            }
        }

        return null;
    }
}

<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph;
use Fhaculty\Graph\Graph;
use PHPStan\Reflection\ClassReflection;

class DependencyGraphFactory
{
    /**
     * @var ClassReflection[] $classReflections
     */
    protected $classes = [];

    /**
     * @var array $dependencyMap
     */
    protected $dependencyMap = [];

    public function addDependency(ClassReflection $depender, ClassReflection $dependee)
    {
        $dependerId = $this->getClassReflectionId($depender);
        $dependeeId = $this->getClassReflectionId($dependee);

        if (!isset($this->dependencyMap[$dependerId])) {
            $this->dependencyMap[$dependerId] = [$dependeeId];
        } elseif (!in_array($dependeeId, $this->dependencyMap[$dependerId])) {
            $this->dependencyMap[$dependerId][] = $dependeeId;
        }
    }

    protected function getClassReflectionId(ClassReflection $classReflection): int
    {
        foreach ($this->classes as $id => $reflection) {
            if ($reflection->getDisplayName() === $classReflection->getDisplayName()) {
                return $id;
            }
        }

        $this->classes[] = $classReflection;

        return count($this->classes) - 1;
    }

    public function create(): DependencyGraph
    {
        $graph = new Graph();

        foreach ($this->classes as $class) {
            $vertex = $graph->createVertex($class->getDisplayName());
            $vertex->setAttribute('reflection', $class);
        }

        foreach ($this->dependencyMap as $dependerId => $dependeeIds) {
            $depender = $graph->getVertex($this->classes[$dependerId]->getDisplayName());
            foreach ($dependeeIds as $dependeeId) {
                $dependee = $graph->getVertex($this->classes[$dependeeId]->getDisplayName());
                $depender->createEdgeTo($dependee);
            }
        }

        return new DependencyGraph($graph);
    }
}

<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder\UnknownClassReflection;
use DependencyAnalyzer\DependencyGraph;
use Fhaculty\Graph\Graph;
use PHPStan\Reflection\ClassReflection;

class DependencyGraphBuilder
{
    /**
     * @var ClassReflection[]|UnknownClassReflection[] $classReflections
     */
    protected $classes = [];

    /**
     * @var array $dependencyMap
     */
    protected $dependencyMap = [];

    /**
     * @var ExtraPhpDocTagResolver
     */
    protected $extraPhpDocTagResolver;

    public function __construct(ExtraPhpDocTagResolver $extraPhpDocTagResolver)
    {
        $this->extraPhpDocTagResolver = $extraPhpDocTagResolver;
    }

    /**
     * @param ClassReflection $depender
     * @param ClassReflection $dependee
     */
    public function addDependency(ClassReflection $depender, ClassReflection $dependee)
    {
        $this->addDependencyMap($this->getClassReflectionId($depender), $this->getClassReflectionId($dependee));
    }

    public function addUnknownDependency(ClassReflection $depender, string $dependeeName)
    {
        $unknownClassReflection = new UnknownClassReflection($dependeeName);
        $unknownClassReflection->addDepender($depender->getDisplayName());
        $this->addDependencyMap($this->getClassReflectionId($depender), $this->getClassReflectionId($unknownClassReflection));
    }

    protected function addDependencyMap(int $dependerId, int $dependeeId)
    {
        if (!isset($this->dependencyMap[$dependerId])) {
            $this->dependencyMap[$dependerId] = [$dependeeId];
        } elseif (!in_array($dependeeId, $this->dependencyMap[$dependerId])) {
            $this->dependencyMap[$dependerId][] = $dependeeId;
        }
    }

    /**
     * @param ClassReflection|UnknownClassReflection $classReflection
     * @return int
     */
    protected function getClassReflectionId($classReflection): int
    {
        foreach ($this->classes as $id => $reflection) {
            if ($reflection->getDisplayName() === $classReflection->getDisplayName()) {
                if ($reflection instanceof UnknownClassReflection) {
                    $reflection->mergeDepender($classReflection);
                }

                return $id;
            }
        }

        $this->classes[] = $classReflection;
        return count($this->classes) - 1;
    }

    public function build(): DependencyGraph
    {
        $graph = new Graph();

        foreach ($this->classes as $class) {
            $vertex = $graph->createVertex($class->getDisplayName());
            $vertex->setAttribute('reflection', $class);
            $canOnlyUsedByTags = ($class instanceof ClassReflection) ? $this->extraPhpDocTagResolver->resolveCanOnlyUsedByTag($class) : [];
            $vertex->setAttribute(ExtraPhpDocTagResolver::ONLY_USED_BY_TAGS, $canOnlyUsedByTags);
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

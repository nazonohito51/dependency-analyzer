<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraphBuilder\UnknownClassReflection;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;
use Fhaculty\Graph\Edge\Base;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use PHPStan\Reflection\ClassReflection;

class DependencyGraphBuilder
{
    protected $graph;

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
        $this->graph = new Graph;
        $this->extraPhpDocTagResolver = $extraPhpDocTagResolver;
    }

    protected function getVertex(\ReflectionClass $class): Vertex
    {
        if ($this->graph->hasVertex($class->getName())) {
            return $this->graph->getVertex($class->getName());
        }

        $vertex = $this->graph->createVertex($class->getName());
        $vertex->setAttribute('reflection', $class);
        $canOnlyUsedByTags = $this->extraPhpDocTagResolver->resolveCanOnlyUsedByTag($class);
        $vertex->setAttribute(ExtraPhpDocTagResolver::ONLY_USED_BY_TAGS, $canOnlyUsedByTags);

        return $vertex;
    }

    /**
     * @param ClassReflection $dependerReflection
     * @param ClassReflection $dependeeReflection
     */
    public function addDependency(ClassReflection $dependerReflection, ClassReflection $dependeeReflection): void
    {
        $depender = $this->getVertex($dependerReflection->getNativeReflection());
        $dependee = $this->getVertex($dependeeReflection->getNativeReflection());

        $edge = $depender->createEdgeTo($dependee);
        $edge->setAttribute('type', DependencyGraph::TYPE_SOME_DEPENDENCY);
    }

    public function addUnknownDependency(ClassReflection $depender, string $dependeeName)
    {
        $unknownClassReflection = new UnknownClassReflection($dependeeName);
        $unknownClassReflection->addDepender($depender->getDisplayName());
        $this->addDependencyMap($this->getClassReflectionId($depender), $this->getClassReflectionId($unknownClassReflection));
    }

    public function addMethodCall(ClassReflection $depender, ClassReflection $dependee, string $methodName)
    {
        // TODO
        $this->addDependencyMap($this->getClassReflectionId($depender), $this->getClassReflectionId($dependee), 'method_call', $methodName);

        // depender = ClassReflection
        // depender part = hoge method
        //
        // dependency type = method call/property fetch/ constant fetch
        //
        // dependee = ClassReflection
        // dependee part = fuga method/fuga property/fugaconstant
    }

    public function addPropertyFetch()
    {
        $this->addDependencyMap($this->getClassReflectionId($depender), $this->getClassReflectionId($dependee), 'property_fetch', $propertyName);
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
        return new DependencyGraph($this->graph);

//        $graph = $this->graph;
//
//        foreach ($this->classes as $class) {
//            $vertex = $graph->createVertex($class->getDisplayName());
//            $vertex->setAttribute('reflection', $class);
//            $canOnlyUsedByTags = ($class instanceof ClassReflection) ? $this->extraPhpDocTagResolver->resolveCanOnlyUsedByTag($class) : [];
//            $vertex->setAttribute(ExtraPhpDocTagResolver::ONLY_USED_BY_TAGS, $canOnlyUsedByTags);
//        }
//
//        foreach ($this->dependencyMap as $dependerId => $dependeeIds) {
//            $depender = $graph->getVertex($this->classes[$dependerId]->getDisplayName());
//            foreach ($dependeeIds as $dependeeId) {
//                $dependee = $graph->getVertex($this->classes[$dependeeId]->getDisplayName());
//                $depender->createEdgeTo($dependee);
//            }
//        }
//
//        return new DependencyGraph($graph);
    }
}

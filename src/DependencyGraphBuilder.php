<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraph\DependencyTypes\Base as DependencyType;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ConstantFetch;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ExtendsClass;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ImplementsClass;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\MethodCall;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\NewObject;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\PropertyFetch;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\SomeDependency;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\UseTrait;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;
use DependencyAnalyzer\DependencyGraphBuilder\UnknownReflectionClass;
use DependencyAnalyzer\Exceptions\LogicException;
use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use PHPStan\Reflection\ClassReflection;
use ReflectionClass;

class DependencyGraphBuilder
{
    protected $graph;

    /**
     * @var ClassReflection[] $classReflections
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

    protected function getVertex(ReflectionClass $class): Vertex
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

    protected function getUnknownClassVertex(string $className): Vertex
    {
        if ($this->graph->hasVertex($className)) {
            $vertex = $this->graph->getVertex($className);
            if (!$vertex->getAttribute('reflection') instanceof UnknownReflectionClass) {
                throw new LogicException("{$className} is not UnknownClassReflection");
            }

            return $vertex;
        }

        $vertex = $this->graph->createVertex($className);
        $vertex->setAttribute('reflection', new UnknownReflectionClass($className));
        $vertex->setAttribute(ExtraPhpDocTagResolver::ONLY_USED_BY_TAGS, []);

        return $vertex;
    }

    protected function addDependencyType(Vertex $depender, Vertex $dependee, DependencyType $additional): void
    {
        if (count($edges = $depender->getEdgesTo($dependee)) === 0) {
            $depender->createEdgeTo($dependee);
        }

        $edge = $depender->getEdgesTo($dependee)->getEdgeFirst();
        $types = $edge->getAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY) ?? [];

        foreach ($types as $type) {
            /** @var DependencyType $type */
            if ($type->isEqual($additional)) {
                return;
            }
        }

        $types[] = $additional;
        $edge->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, $types);
    }

    /**
     * @param ReflectionClass $dependerReflection
     * @param ReflectionClass $dependeeReflection
     */
    public function addDependency(ReflectionClass $dependerReflection, ReflectionClass $dependeeReflection): void
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getVertex($dependeeReflection),
            new SomeDependency()
        );
    }

    public function addUnknownDependency(ReflectionClass $dependerReflection, string $dependeeName)
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getUnknownClassVertex($dependeeName),
            new SomeDependency()
        );
    }

    public function addNew(ReflectionClass $dependerReflection, ReflectionClass $dependeeReflection, string $caller = null)
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getVertex($dependeeReflection),
            new NewObject($caller)
        );
    }

    public function addMethodCall(ReflectionClass $dependerReflection, ReflectionClass $dependeeReflection, string $callee, string $caller = null)
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getVertex($dependeeReflection),
            new MethodCall($callee, $caller)
        );
    }

    public function addPropertyFetch(ReflectionClass $dependerReflection, ReflectionClass $dependeeReflection, string $propertyName, string $caller = null)
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getVertex($dependeeReflection),
            new PropertyFetch($propertyName, $caller)
        );
    }

    public function addConstFetch(ReflectionClass $dependerReflection, ReflectionClass $dependeeReflection, string $constantName, string $caller = null)
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getVertex($dependeeReflection),
            new ConstantFetch($constantName, $caller)
        );
    }

    public function addExtends(ReflectionClass $dependerReflection, ReflectionClass $dependeeReflection)
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getVertex($dependeeReflection),
            new ExtendsClass()
        );
    }

    public function addImplements(ReflectionClass $dependerReflection, ReflectionClass $dependeeReflection)
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getVertex($dependeeReflection),
            new ImplementsClass()
        );
    }

    public function addUseTrait(ReflectionClass $dependerReflection, ReflectionClass $dependeeReflection)
    {
        $this->addDependencyType(
            $this->getVertex($dependerReflection),
            $this->getVertex($dependeeReflection),
            new UseTrait()
        );
    }

    public function build(): DependencyGraph
    {
        return new DependencyGraph($this->graph);
    }
}

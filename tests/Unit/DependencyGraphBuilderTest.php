<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ConstantFetch;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ExtendsClass;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ImplementsClass;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\MethodCall;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\NewObject;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\PropertyFetch;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\SomeDependency;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\UseTrait;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;
use DependencyAnalyzer\DependencyGraphBuilder;
use DependencyAnalyzer\DependencyGraphBuilder\UnknownReflectionClass;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class DependencyGraphBuilderTest extends TestCase
{
    public function testAddDependency()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');
        $nativeClassReflection3 = $this->createNativeClassReflection('v3');

        $builder->addDependency($nativeClassReflection1, $nativeClassReflection2);
        $builder->addDependency($nativeClassReflection2, $nativeClassReflection3);
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $v3 = $graph->createVertex('v3');
        $e1 = $v1->createEdgeTo($v2);
        $e2 = $v2->createEdgeTo($v3);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $nativeClassReflection2);
        $v2->setAttribute('@canOnlyUsedBy', []);
        $v3->setAttribute('reflection', $nativeClassReflection3);
        $v3->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $e2->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddUnknownDependency()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');

        $builder->addUnknownDependency($nativeClassReflection1, 'v2');
        $builder->addUnknownDependency($nativeClassReflection1, 'v3');
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $v3 = $graph->createVertex('v3');
        $e1 = $v1->createEdgeTo($v2);
        $e2 = $v1->createEdgeTo($v3);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', new UnknownReflectionClass('v2'));
        $v2->setAttribute('@canOnlyUsedBy', []);
        $v3->setAttribute('reflection', new UnknownReflectionClass('v3'));
        $v3->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $e2->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddNew()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');

        $builder->addNew($nativeClassReflection1, $nativeClassReflection2, 'someV1Method');
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $e1 = $v1->createEdgeTo($v2);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $nativeClassReflection2);
        $v2->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new NewObject('someV1Method')]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddMethodCall()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');

        $builder->addMethodCall($nativeClassReflection1, $nativeClassReflection2, 'someV2Method', 'someV1Method');
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $e1 = $v1->createEdgeTo($v2);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $nativeClassReflection2);
        $v2->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new MethodCall('someV2Method', 'someV1Method')]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddPropertyFetch()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');

        $builder->addPropertyFetch($nativeClassReflection1, $nativeClassReflection2, 'someV2Property', 'someV1Method');
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $e1 = $v1->createEdgeTo($v2);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $nativeClassReflection2);
        $v2->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new PropertyFetch('someV2Property', 'someV1Method')]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddConstFetch()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');

        $builder->addConstFetch($nativeClassReflection1, $nativeClassReflection2, 'someV2Constant', 'someV1Method');
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $e1 = $v1->createEdgeTo($v2);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $nativeClassReflection2);
        $v2->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new ConstantFetch('someV2Constant', 'someV1Method')]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddExtends()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');

        $builder->addExtends($nativeClassReflection1, $nativeClassReflection2);
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $e1 = $v1->createEdgeTo($v2);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $nativeClassReflection2);
        $v2->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new ExtendsClass()]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddImplements()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('interface1');

        $builder->AddImplements($nativeClassReflection1, $nativeClassReflection2);
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('interface1');
        $e1 = $v1->createEdgeTo($v2);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $nativeClassReflection2);
        $v2->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new ImplementsClass()]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddUseTrait()
    {
        $builder = new DependencyGraphBuilder($this->createExtraPhpDocTagResolver());
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('trait1');

        $builder->AddUseTrait($nativeClassReflection1, $nativeClassReflection2);
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('trait1');
        $e1 = $v1->createEdgeTo($v2);
        $v1->setAttribute('reflection', $nativeClassReflection1);
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $nativeClassReflection2);
        $v2->setAttribute('@canOnlyUsedBy', []);
        $e1->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new UseTrait()]);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    protected function createNativeClassReflection(string $displayName)
    {
        $nativeClassReflection = $this->createMock(\ReflectionClass::class);
        $nativeClassReflection->method('getName')->willReturn($displayName);

        return $nativeClassReflection;
    }

    protected function createExtraPhpDocTagResolver(array $returnTags = [])
    {
        $extraPhpDocTagResolver = $this->createMock( ExtraPhpDocTagResolver::class);
        $extraPhpDocTagResolver->method('resolveCanOnlyUsedByTag')->willReturn([]);

        return $extraPhpDocTagResolver;
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;
use DependencyAnalyzer\DependencyGraphBuilder;
use DependencyAnalyzer\DependencyGraphBuilder\UnknownReflectionClass;
use Fhaculty\Graph\Graph;
use PHPStan\Reflection\ClassReflection;
use Tests\TestCase;

class DependencyGraphBuilderTest extends TestCase
{
    public function testAddDependency()
    {
        $extraPhpDocTagResolver = $this->createMock( ExtraPhpDocTagResolver::class);
        $extraPhpDocTagResolver->method('resolveCanOnlyUsedByTag')->willReturn([]);
        $builder = new DependencyGraphBuilder($extraPhpDocTagResolver);

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
        $e1->setAttribute('type', DependencyGraph::TYPE_SOME_DEPENDENCY);
        $e2->setAttribute('type', DependencyGraph::TYPE_SOME_DEPENDENCY);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddUnknownDependency()
    {
        $extraPhpDocTagResolver = $this->createMock( ExtraPhpDocTagResolver::class);
        $extraPhpDocTagResolver->method('resolveCanOnlyUsedByTag')->willReturn([]);
        $builder = new DependencyGraphBuilder($extraPhpDocTagResolver);

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
        $e1->setAttribute('type', DependencyGraph::TYPE_SOME_DEPENDENCY);
        $e2->setAttribute('type', DependencyGraph::TYPE_SOME_DEPENDENCY);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddMethodCall()
    {
        $extraPhpDocTagResolver = $this->createMock( ExtraPhpDocTagResolver::class);
        $extraPhpDocTagResolver->method('resolveCanOnlyUsedByTag')->willReturn([]);
        $builder = new DependencyGraphBuilder($extraPhpDocTagResolver);

        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');
        $nativeClassReflection3 = $this->createNativeClassReflection('v3');
        $builder->addMethodCall($nativeClassReflection1, $nativeClassReflection2, 'someV2Method', 'someV1Method');
        $builder->addMethodCall($nativeClassReflection2, $nativeClassReflection3, 'someV3Method', 'someV2Method');
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
        $e1->setAttribute('type', DependencyGraph::TYPE_METHOD_CALL);
        $e1->setAttribute('caller', 'someV1Method');
        $e1->setAttribute('callee', 'someV2Method');
        $e2->setAttribute('type', DependencyGraph::TYPE_METHOD_CALL);
        $e2->setAttribute('caller', 'someV2Method');
        $e2->setAttribute('callee', 'someV3Method');
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    public function testAddPropertyFetch()
    {
        $extraPhpDocTagResolver = $this->createMock( ExtraPhpDocTagResolver::class);
        $extraPhpDocTagResolver->method('resolveCanOnlyUsedByTag')->willReturn([]);
        $builder = new DependencyGraphBuilder($extraPhpDocTagResolver);

        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');
        $nativeClassReflection3 = $this->createNativeClassReflection('v3');
        $builder->addPropertyFetch($nativeClassReflection1, $nativeClassReflection2, 'someV2Property', 'someV1Method');
        $builder->addPropertyFetch($nativeClassReflection2, $nativeClassReflection3, 'someV3Property', 'someV2Method');
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
        $e1->setAttribute('type', DependencyGraph::TYPE_PROPERTY_FETCH);
        $e1->setAttribute('property', 'someV2Property');
        $e1->setAttribute('caller', 'someV1Method');
        $e2->setAttribute('type', DependencyGraph::TYPE_PROPERTY_FETCH);
        $e2->setAttribute('property', 'someV3Property');
        $e2->setAttribute('caller', 'someV2Method');
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    protected function createNativeClassReflection(string $displayName)
    {
        $nativeClassReflection = $this->createMock(\ReflectionClass::class);
        $nativeClassReflection->method('getName')->willReturn($displayName);

        return $nativeClassReflection;
    }
}

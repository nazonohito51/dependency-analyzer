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

        // TODO: Usageを見る限り、ClassReflectionではなくNativeのReflectionクラスで良さそうなので、テスト実装後に引数を変更する
        $nativeClassReflection1 = $this->createNativeClassReflection('v1');
        $nativeClassReflection2 = $this->createNativeClassReflection('v2');
        $nativeClassReflection3 = $this->createNativeClassReflection('v3');
        $classReflection1 = $this->createClassReflection($nativeClassReflection1);
        $classReflection2 = $this->createClassReflection($nativeClassReflection2);
        $classReflection3 = $this->createClassReflection($nativeClassReflection3);
        $builder->addDependency($classReflection1, $classReflection2);
        $builder->addDependency($classReflection2, $classReflection3);
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
        $classReflection1 = $this->createClassReflection($nativeClassReflection1);
        $builder->addUnknownDependency($classReflection1, 'v2');
        $builder->addUnknownDependency($classReflection1, 'v3');
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

//    public function testAddCallMethodDependency()
//    {
//        $extraPhpDocTagResolver = $this->createMock( ExtraPhpDocTagResolver::class);
//        $extraPhpDocTagResolver->method('resolveCanOnlyUsedByTag')->willReturn([]);
//        $builder = new DependencyGraphBuilder($extraPhpDocTagResolver);
//
//        $builder->addDependency($this->createClassReflection('v1'), $this->createClassReflection('v2'));
//        $builder->addDependency($this->createClassReflection('v2'), $this->createClassReflection('v3'));
//        $dependencyGraph = $builder->build();
//
//        $graph = new Graph();
//        $v1 = $graph->createVertex('v1');
//        $v2 = $graph->createVertex('v2');
//        $v3 = $graph->createVertex('v3');
//        $e1 = $v1->createEdgeTo($v2);
//        $e2 = $v2->createEdgeTo($v3);
//        $v1->setAttribute('reflection', $this->createClassReflection('v1'));
//        $v1->setAttribute('@canOnlyUsedBy', []);
//        $v2->setAttribute('reflection', $this->createClassReflection('v2'));
//        $v2->setAttribute('@canOnlyUsedBy', []);
//        $v3->setAttribute('reflection', $this->createClassReflection('v3'));
//        $v3->setAttribute('@canOnlyUsedBy', []);
//        $e1->setAttribute('type', DependencyGraph::TYPE_METHOD_CALL);
//        $e1->setAttribute('depender part', 'dependerMethod1');
//        $e1->setAttribute('dependee part', 'dependeeMethod1');
//        $e2->setAttribute('type', DependencyGraph::TYPE_METHOD_CALL);
//        $e2->setAttribute('depender part', 'dependerMethod2');
//        $e2->setAttribute('dependee part', 'dependeeMethod2');
//        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
//    }

    protected function createNativeClassReflection(string $displayName)
    {
        $nativeClassReflection = $this->createMock(\ReflectionClass::class);
        $nativeClassReflection->method('getName')->willReturn($displayName);

        return $nativeClassReflection;
    }

    protected function createClassReflection(\ReflectionClass $nativeClassReflection)
    {
        $classReflection = $this->createMock(ClassReflection::class);
        $classReflection->method('getNativeReflection')->willReturn($nativeClassReflection);

        return $classReflection;
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;
use DependencyAnalyzer\DependencyGraphBuilder;
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
        $builder->addDependency($this->createClassReflection('v1'), $this->createClassReflection('v2'));
        $builder->addDependency($this->createClassReflection('v2'), $this->createClassReflection('v3'));
        $dependencyGraph = $builder->build();

        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $v3 = $graph->createVertex('v3');
        $v1->createEdgeTo($v2);
        $v2->createEdgeTo($v3);
        $v1->setAttribute('reflection', $this->createClassReflection('v1'));
        $v1->setAttribute('@canOnlyUsedBy', []);
        $v2->setAttribute('reflection', $this->createClassReflection('v2'));
        $v2->setAttribute('@canOnlyUsedBy', []);
        $v3->setAttribute('reflection', $this->createClassReflection('v3'));
        $v3->setAttribute('@canOnlyUsedBy', []);
        $this->assertGraphEquals($graph, $dependencyGraph->getGraph());
    }

    protected function createClassReflection(string $displayName)
    {
        $classReflection = $this->createMock(ClassReflection::class);
        $classReflection->method('getDisplayName')->willReturn($displayName);

        return $classReflection;
    }
}

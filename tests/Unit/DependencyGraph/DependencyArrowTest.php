<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\DependencyArrow;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ConstantFetch;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ExtendsClass;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\ImplementsClass;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\MethodCall;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\NewObject;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\PropertyFetch;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\SomeDependency;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\UseTrait;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Vertex;
use Tests\TestCase;

class DependencyArrowTest extends TestCase
{
    public function provideGetDependencies()
    {
        return [
            [[new SomeDependency()], ['\SomeDepender', '\SomeDependee']],
            [[new MethodCall('calleeMethod', 'callerMethod')], ['\SomeDepender::callerMethod()', '\SomeDependee::calleeMethod()']],
            [[new PropertyFetch('calleeProperty', 'callerMethod')], ['\SomeDepender::callerMethod()', '\SomeDependee::$calleeProperty']],
            [[new ConstantFetch('CALLEE_CONSTANT', 'callerMethod')], ['\SomeDepender::callerMethod()', '\SomeDependee::CALLEE_CONSTANT']],
            [[new NewObject('callerMethod')], ['\SomeDepender::callerMethod()', '\SomeDependee::__construct()']],
            [[new ExtendsClass()], ['\SomeDepender', '\SomeDependee']],
            [[new ImplementsClass()], ['\SomeDepender', '\SomeDependee']],
            [[new UseTrait()], ['\SomeDepender', '\SomeDependee']],
        ];
    }

    /**
     * @param array $dependencyTypes
     * @param array $expected
     * @dataProvider provideGetDependencies
     */
    public function testGetDependencies(array $dependencyTypes, array $expected)
    {
        $depender = $this->createMock(Vertex::class);
        $depender->method('getId')->willReturn('\SomeDepender');
        $dependee = $this->createMock(Vertex::class);
        $dependee->method('getId')->willReturn('\SomeDependee');
        $edge = $this->createMock(Directed::class);
        $edge->method('getVertexStart')->willReturn($depender);
        $edge->method('getVertexEnd')->willReturn($dependee);
        $edge->method('getAttribute')->with(DependencyGraph::DEPENDENCY_TYPE_KEY)->willReturn($dependencyTypes);
        $sut = new DependencyArrow($edge);

        $actual = $sut->getDependencies();

        $this->assertCount(1, $actual);
        $this->assertCount(2, $actual[0]);
        $this->assertInstanceOf(Base::class, $actual[0][0]);
        $this->assertInstanceOf(Base::class, $actual[0][1]);
        $this->assertSame($expected[0], $actual[0][0]->toString());
        $this->assertSame($expected[1], $actual[0][1]->toString());
    }
}

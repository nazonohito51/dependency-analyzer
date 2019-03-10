<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyDumper\DependencyResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use Tests\TestCase;

class DependencyResolverTest extends TestCase
{
    public function testResolveDependencies()
    {
        $reflection1 = $this->createMock(ClassReflection::class);
        $reflection2 = $this->createMock(ClassReflection::class);
        $reflection3 = $this->createMock(ClassReflection::class);

        $broker = $this->createMock(Broker::class);
        $broker->method('getClass')->willReturnMap([
            ['SomeClass', $reflection1],
            ['SomeInterface1', $reflection2],
            ['SomeInterface2', $reflection3],
        ]);
        $dependencyResolver = new DependencyResolver($broker);

        $node = $this->createMock(\PhpParser\Node\Stmt\Class_::class);
        $node->extends = $this->createNameNodeMock('SomeClass');
        $node->implements = [
            $this->createNameNodeMock('SomeInterface1'),
            $this->createNameNodeMock('SomeInterface2')
        ];
        $scope = $this->createMock(Scope::class);

        $dependencies = $dependencyResolver->resolveDependencies($node, $scope);

        $this->assertEquals([$reflection1, $reflection2, $reflection3], $dependencies);
    }

    public function testResolveDependencies1()
    {
        $reflection1 = $this->createMock(ClassReflection::class);
        $reflection2 = $this->createMock(ClassReflection::class);

        $broker = $this->createMock(Broker::class);
        $broker->method('getClass')->willReturnMap([
            ['SomeInterface1', $reflection1],
            ['SomeInterface2', $reflection2]
        ]);
        $dependencyResolver = new DependencyResolver($broker);

        $node = $this->createMock(\PhpParser\Node\Stmt\Interface_::class);
        $someInterface1 = $this->createNameNodeMock('SomeInterface1');
        $someInterface2 = $this->createNameNodeMock('SomeInterface2');
        $node->extends = [$someInterface1, $someInterface2];
        $scope = $this->createMock(Scope::class);

        $dependencies = $dependencyResolver->resolveDependencies($node, $scope);

        $this->assertEquals([$reflection1, $reflection2], $dependencies);
    }

    protected function addClassReflectionToBroker(string $className)
    {
        $reflection1 = $this->createMock(ClassReflection::class);

        $broker = $this->createMock(Broker::class);
        $broker->method('getClass')->with($className)->willReturn($reflection1);
    }

    protected function createNameNodeMock(string $name)
    {
        $nameNode = $this->createMock(\PhpParser\Node\Name::class);
        $nameNode->method('toString')->willReturn($name);

        return $nameNode;
    }



//    public function testResolveDependencies()
//    {
//        $className1 = 'className1';
//        $className2 = 'className2';
//        $className3 = 'className3';
//        $classReflection1 = $this->createMock(ClassReflection::class);
//        $classReflection2 = $this->createMock(ClassReflection::class);
//        $classReflection3 = $this->createMock(ClassReflection::class);
//        $broker = $this->createMock(Broker::class);
//        $broker->method('getClass')->will($this->returnValueMap([
//            [$className1, $classReflection1],
//            [$className2, $classReflection2],
//            [$className3, $classReflection3]
//        ]));
//        $resolver = new DependencyResolver($broker);
//        $node = $this->createMock(Class_::class);
//        $node->extends = $this->createStringableClass($className1);
//        $node->implements = [$this->createStringableClass($className2), $this->createStringableClass($className3)];
//        $scope = $this->createMock(Scope::class);
//
//        $dependencies = $resolver->resolveDependencies($node, $scope);
//
//        $this->assertEquals([$classReflection1, $classReflection2, $classReflection3], $dependencies);
//    }
//
//    protected function createStringableClass(string $className)
//    {
//        return new class ($className) {
//            private $className;
//
//            public function __construct(string $className)
//            {
//                $this->className = $className;
//            }
//            public function toString()
//            {
//                return $this->className;
//            }
//        };
//    }
}

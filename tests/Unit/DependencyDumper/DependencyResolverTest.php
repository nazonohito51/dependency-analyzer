<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyDumper\DependencyResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
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
        $lexer = $this->createMock(Lexer::class);
        $phpDocParser = $this->createMock(PhpDocParser::class);
        $dependencyResolver = new DependencyResolver($broker, $lexer, $phpDocParser);

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
        $lexer = $this->createMock(Lexer::class);
        $phpDocParser = $this->createMock(PhpDocParser::class);
        $dependencyResolver = new DependencyResolver($broker, $lexer, $phpDocParser);

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
}

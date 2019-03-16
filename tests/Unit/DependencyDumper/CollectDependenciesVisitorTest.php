<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyDumper\DependencyResolver;
use DependencyAnalyzer\DependencyDumper\CollectDependenciesVisitor;
use DependencyAnalyzer\DependencyGraph\ClassLike;
use DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder;
use DependencyAnalyzer\Exceptions\UnexpectedException;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Reflection\ReflectionWithFilename;
use Tests\TestCase;

class CollectDependenciesVisitorTest extends TestCase
{
    public function provideInvoke()
    {
        $scopeOwnerName = 'SomeNamespace\SomeClass1';
        $someClassName = 'SomeNamespace\SomeClass2';

        $scopeOwner = $this->createClassReflection($scopeOwnerName);
        $classSameScopeOwner = $this->createClassReflection($scopeOwnerName);
        $someClass = $this->createClassReflection($someClassName);
        $someFunction = $this->createMock(PhpFunctionReflection::class);

        return [
            'return different class from scope_owner' => [
                $this->createSomeNode(),
                $this->createScope($scopeOwner),
                $this->createDependencyResolver($someClass, $this->createClassReflection($scopeOwnerName)),
                [[$scopeOwner, $someClass]]
            ],
            'return same class as scope_owner' => [
                $this->createSomeNode(),
                $this->createScope($scopeOwner),
                $this->createDependencyResolver($classSameScopeOwner, $this->createClassReflection($scopeOwnerName)),
                []
            ],
            'is not in scope, and node is declare class node' => [
                $this->createDeclareClassNode($scopeOwnerName),
                $this->createScope(),
                $this->createDependencyResolver($someClass, $this->createClassReflection($scopeOwnerName)),
                [[$scopeOwner, $someClass]]
            ],
            'is not in scope, and node is not declare class node' => [
                $this->createSomeNode(),
                $this->createScope(),
                $this->createDependencyResolver($someClass, $this->createClassReflection($scopeOwnerName)),
                []
            ],
            'return function' => [
                $this->createSomeNode(),
                $this->createScope($scopeOwner),
                $this->createDependencyResolver($someFunction, $this->createClassReflection($scopeOwnerName)),
                []
            ]
        ];
    }

    /**
     * @param Node $node
     * @param Scope $scope
     * @param DependencyResolver $dependencyResolver
     * @param array $expected
     * @dataProvider provideInvoke
     */
    public function testInvoke(Node $node, Scope $scope, DependencyResolver $dependencyResolver, array $expected)
    {
        $factory = $this->createMock(DependencyGraphBuilder::class);
        if (count($expected) > 0) {
            foreach ($expected as $classes) {
                $factory->expects($this->once())->method('addDependency')->with($classes[0], $classes[1]);
            }
        } else {
            $factory->expects($this->never())->method('addDependency');
        }
//        $dependencyResolver = $this->createDependencyResolver($resolvedDependency);
        $nodeVisitor = new CollectDependenciesVisitor($dependencyResolver, $factory);

        $nodeVisitor($node, $scope);

//        $this->assertSameClassLike($expected, $nodeVisitor->getDependencies());
    }

    /**
     * @expectedException \DependencyAnalyzer\Exceptions\UnexpectedException
     */
    public function testInvoke_WhenSameClassReflection()
    {
        $node = $this->createSomeNode();
        $scope = $this->createScope();
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        $dependencyResolver->method('resolveDependencies')->willThrowException(new UnexpectedException());
        $dependencyResolver->method('resolveClassReflection')->willReturn($this->createMock(ClassReflection::class));
        $factory = $this->createMock(DependencyGraphBuilder::class);
        $nodeVisitor = new CollectDependenciesVisitor($dependencyResolver, $factory);

        $nodeVisitor($node, $scope);
    }

    protected function createClassReflection(string $className)
    {
        $classReflection = $this->createMock(ClassReflection::class);
        $classReflection->method('getDisplayName')->willReturn($className);

        return $classReflection;
    }

    protected function createScope(ClassReflection $scopeOwner = null)
    {
        $scope = $this->createMock(Scope::class);

        if (!is_null($scopeOwner)) {
            $scope->method('isInClass')->willReturn(true);
            $scope->method('getClassReflection')->willReturn($scopeOwner);
        } else {
            $scope->method('isInClass')->willReturn(false);
        }

        return $scope;
    }

    protected function createSomeNode()
    {
        return $this->createMock(Node::class);
    }

    protected function createDeclareClassNode(string $classFullName)
    {
        $name = $this->createMock(Node\Name::class);
        $name->method('toString')->willReturn($classFullName);

        $node = $this->createMock(Node\Stmt\Class_::class);
        $node->namespacedName = $name;

        return $node;
    }

    /**
     * @param array $expected
     * @param ClassLike[] $actual
     */
    protected function assertSameClassLike(array $expected, array $actual)
    {
        $this->assertCount(count($expected), $actual);
        $actual = array_reduce($actual, function (array $carry, ClassLike $classLike) {
            return array_merge($carry, $classLike->toArray());
        }, []);
        $this->assertSame($expected, $actual);
    }

    /**
     * @param ReflectionWithFilename $resolvedDependency
     * @param ClassReflection $classReflection
     * @return DependencyResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createDependencyResolver(ReflectionWithFilename $resolvedDependency, ClassReflection $classReflection)
    {
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        $dependencyResolver->method('resolveDependencies')->willReturn([$resolvedDependency]);
        $dependencyResolver->method('resolveClassReflection')->willReturn($classReflection);
        return $dependencyResolver;
    }
}

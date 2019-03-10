<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyDumper\DependencyResolver;
use DependencyAnalyzer\DependencyDumper\CollectDependenciesVisitor;
use PhpParser\Node;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\Scope;
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
                $someClass,
                [$scopeOwnerName => [$someClassName]]
            ],
            'return same class as scope_owner' => [
                $this->createSomeNode(),
                $this->createScope($scopeOwner),
                $classSameScopeOwner,
                []
            ],
            'is not in scope, and node is declare class node' => [
                $this->createDeclareClassNode($scopeOwnerName),
                $this->createScope(),
                $someClass,
                [$scopeOwnerName => [$someClassName]]
            ],
            'is not in scope, and node is not declare class node' => [
                $this->createSomeNode(),
                $this->createScope(),
                $someClass,
                []
            ],
            'return function' => [
                $this->createSomeNode(),
                $this->createScope($scopeOwner),
                $someFunction,
                []
            ]
        ];
    }

    /**
     * @param Node $node
     * @param Scope $scope
     * @param ReflectionWithFilename $resolvedDependency
     * @param array $expected
     * @dataProvider provideInvoke
     */
    public function testInvoke(Node $node, Scope $scope, ReflectionWithFilename $resolvedDependency, array $expected)
    {
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        $dependencyResolver->method('resolveDependencies')->with($node, $scope)->willReturn([$resolvedDependency]);
        $nodeVisitor = new CollectDependenciesVisitor($dependencyResolver);

        $nodeVisitor($node, $scope);

        $this->assertEquals($expected, $nodeVisitor->getDependencies());
    }

    /**
     * @expectedException \DependencyAnalyzer\Exceptions\ResolveDependencyException
     */
    public function testInvoke_WhenSameClassReflection()
    {
        $node = $this->createSomeNode();
        $scope = $this->createScope();
        $exception = $this->createMock(AnalysedCodeException::class);
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        $dependencyResolver->method('resolveDependencies')->willThrowException($exception);
        $nodeVisitor = new CollectDependenciesVisitor($dependencyResolver);

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
}

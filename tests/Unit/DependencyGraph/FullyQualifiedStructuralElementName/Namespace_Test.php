<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Class_;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\ClassConstant;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Constant;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Function_;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Interface_;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Method;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Namespace_;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Property;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Trait_;
use Tests\TestCase;

class Namespace_Test extends TestCase
{
    public function provideInclude()
    {
        return [
            'class1' => [new Class_('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass'), true],
            'class2' => [new Class_('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeNamespace\SomeClass'), true],
            'class3' => [new Class_('\Tests\Fixtures\SomeClass'), false],
            'property1' => [new Property('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::$propertyName'), true],
            'property2' => [new Property('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeNamespace\SomeClass::$propertyName'), true],
            'property3' => [new Property('\Tests\Fixtures\SomeClass1::$propertyName'), false],
            'method1' => [new Method('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass1::methodName()'), true],
            'method2' => [new Method('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeNamespace\SomeClass1::methodName()'), true],
            'method3' => [new Method('\Tests\Fixtures\SomeClass1::methodName()'), false],
            'class_constant1' => [new ClassConstant('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass1::SOME_CONSTANT'), true],
            'class_constant2' => [new ClassConstant('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeNamespace\SomeClass1::SOME_CONSTANT'), true],
            'class_constant3' => [new ClassConstant('\Tests\Fixtures\SomeClass1::SOME_CONSTANT'), false],
            'namespace1' => [new Namespace_('\Tests\Fixtures\FullyQualifiedStructuralElementName\Some\\'), true],
            'namespace2' => [new Namespace_('\Tests\Fixtures\FullyQualifiedStructuralElementName\Some\Namespace\\'), true],
            'namespace3' => [new Namespace_('\Tests\Fixtures\OtherNamespace\\'), false],
            'interface' => [new Interface_('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeInterface1'), true],
            'trait' => [new Trait_('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeTrait1'), true],
            'function' => [new Function_('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeFunction()'), true],
            'constant' => [new Constant('\Tests\Fixtures\FullyQualifiedStructuralElementName\SOME_CONSTANT'), true],
        ];
    }

    /**
     * @param Base $target
     * @param bool $expected
     * @dataProvider provideInclude
     */
    public function testInclude(Base $target, bool $expected)
    {
        $sut = new Namespace_('\Tests\Fixtures\FullyQualifiedStructuralElementName\\');

        $this->assertSame($expected, $sut->include($target));
    }

    public function provideGetFullyQualifiedNamespaceNameAsArray()
    {
        return [
            ['\Tests\Fixtures\FullyQualifiedStructuralElementName\\', ['Tests', 'Fixtures', 'FullyQualifiedStructuralElementName']],
            ['\SomeNamespace\\', ['SomeNamespace']],
            ['\\', []]
        ];
    }

    /**
     * @param string $namespaceName
     * @param array $expected
     * @dataProvider provideGetFullyQualifiedNamespaceNameAsArray
     */
    public function testGetFullyQualifiedNamespaceName(string $namespaceName, array $expected)
    {
        $sut = new Namespace_($namespaceName);

        $this->assertSame($expected, $sut->getFullyQualifiedNamespaceNameAsArray());
    }

    public function provideGetFullyQualifiedClassNameAsArray()
    {
        return [
            ['\Tests\Fixtures\FullyQualifiedStructuralElementName\\'],
            ['\SomeNamespace\\'],
            ['\\'],
        ];
    }

    /**
     * @param string $namespaceName
     * @dataProvider provideGetFullyQualifiedClassNameAsArray
     */
    public function testGetFullyQualifiedClassName(string $namespaceName)
    {
        $sut = new Namespace_($namespaceName);

        $this->assertNull($sut->getFullyQualifiedClassNameAsArray());
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName as FQSEN;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;
use Tests\TestCase;

class FullyQualifiedStructuralElementNameTest extends TestCase
{
    public function testCreateNamespace()
    {
        $fqsen = FQSEN::createNamespace('\Tests\Fixtures\FullyQualifiedStructuralElementName\\');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\\', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_NAMESPACE, $fqsen->getType());
        $this->assertTrue($fqsen->isNamespace());
        $this->assertFalse($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertFalse($fqsen->isConstant());
    }

    public function testCreateClass()
    {
        $fqsen = FQSEN::createClass('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_CLASS, $fqsen->getType());
        $this->assertFalse($fqsen->isNamespace());
        $this->assertTrue($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertFalse($fqsen->isConstant());
    }

    public function testCreateMethod()
    {
        $fqsen = FQSEN::createMethod('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass', 'methodName');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::methodName()', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_METHOD, $fqsen->getType());
        $this->assertFalse($fqsen->isNamespace());
        $this->assertFalse($fqsen->isClass());
        $this->assertTrue($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertFalse($fqsen->isConstant());
    }

    public function testCreateProperty()
    {
        $fqsen = FQSEN::createProperty('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass', 'propertyName');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::$propertyName', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_PROPERTY, $fqsen->getType());
        $this->assertFalse($fqsen->isNamespace());
        $this->assertFalse($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertTrue($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertFalse($fqsen->isConstant());
    }

    public function testCreateClassConstant()
    {
        $fqsen = FQSEN::createClassConstant('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass', 'CLASS_CONSTANT_NAME');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::CLASS_CONSTANT_NAME', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_CLASS_CONSTANT, $fqsen->getType());
        $this->assertFalse($fqsen->isNamespace());
        $this->assertFalse($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertTrue($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertFalse($fqsen->isConstant());
    }

    public function testCreateInterface()
    {
        $fqsen = FQSEN::createInterface('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeInterface');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeInterface', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_INTERFACE, $fqsen->getType());
        $this->assertFalse($fqsen->isNamespace());
        $this->assertFalse($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertTrue($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertFalse($fqsen->isConstant());
    }

    public function testCreateTrait()
    {
        $fqsen = FQSEN::createTrait('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeTrait');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeTrait', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_TRAIT, $fqsen->getType());
        $this->assertFalse($fqsen->isNamespace());
        $this->assertFalse($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertTrue($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertFalse($fqsen->isConstant());
    }

    public function testCreateFunction()
    {
        $fqsen = FQSEN::createFunction('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeFunction');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeFunction()', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_FUNCTION, $fqsen->getType());
        $this->assertFalse($fqsen->isNamespace());
        $this->assertFalse($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertTrue($fqsen->isFunction());
        $this->assertFalse($fqsen->isConstant());
    }

    public function testCreateConstant()
    {
        $fqsen = FQSEN::createConstant('\Tests\Fixtures\FullyQualifiedStructuralElementName\SOME_CONSTANT');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SOME_CONSTANT', $fqsen->toString());
        $this->assertSame(FQSEN::TYPE_CONSTANT, $fqsen->getType());
        $this->assertFalse($fqsen->isNamespace());
        $this->assertFalse($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertTrue($fqsen->isConstant());
    }

    public function provideCreateFromString()
    {
        return [
            'namespace' => ['\SomeClass\\', FQSEN::TYPE_NAMESPACE],
            'class1' => ['\SomeClass', FQSEN::TYPE_CLASS],
            'class2' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass', FQSEN::TYPE_CLASS],
            'method' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::methodName()', FQSEN::TYPE_METHOD],
            'property' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::$propertyName', FQSEN::TYPE_PROPERTY],
            'class_constant' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::CLASS_CONSTANT_NAME', FQSEN::TYPE_CLASS_CONSTANT],
            'interface' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeInterface', FQSEN::TYPE_CLASS],
            'trait' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeTrait', FQSEN::TYPE_CLASS],
            'function' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeFunction()', FQSEN::TYPE_FUNCTION],
            'constant' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SOME_CONSTANT', FQSEN::TYPE_CLASS],
        ];
    }

    /**
     * @param string $elementString
     * @param string $expectedType
     * @dataProvider provideCreateFromString
     */
    public function testCreateFromString(string $elementString, string $expectedType)
    {
        $fqsen = FQSEN::createFromString($elementString);

        $this->assertSame($elementString, $fqsen->toString());
        $this->assertSame($expectedType, $fqsen->getType());
    }

    public function provideCreateFromStringWithInvalidArgument()
    {
        return [
            'invalid_fqcn' => ['SomeClass'],
            'invalid_method1' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::methodName('],
            'invalid_method2' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::$methodName()'],
            'empty' => ['']
        ];
    }

    /**
     * @param string $elementString
     * @dataProvider provideCreateFromStringWithInvalidArgument
     * @expectedException \DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException
     */
    public function testCreateFromStringWithInvalidArgument(string $elementString)
    {
        FQSEN::createFromString($elementString);
    }

    public function provideCreateFromReflection()
    {
        $reflectionClass = $this->createMock(\ReflectionClass::class);
        $reflectionClass->method('getName')->willReturn('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass');

        $reflectionClass1 = $this->createMock(\ReflectionClass::class);
        $reflectionClass1->method('getName')->willReturn('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass');
        $reflectionMethod = $this->createMock(\ReflectionMethod::class);
        $reflectionMethod->method('getName')->willReturn('someMethod');
        $reflectionMethod->method('getDeclaringClass')->willReturn($reflectionClass1);

        $reflectionClass2 = $this->createMock(\ReflectionClass::class);
        $reflectionClass2->method('getName')->willReturn('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass');
        $reflectionProperty = $this->createMock(\ReflectionProperty::class);
        $reflectionProperty->method('getName')->willReturn('someProperty');
        $reflectionProperty->method('getDeclaringClass')->willReturn($reflectionClass2);

        $reflectionClass3 = $this->createMock(\ReflectionClass::class);
        $reflectionClass3->method('getName')->willReturn('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass');
        $reflectionClassConstant = $this->createMock(\ReflectionClassConstant::class);
        $reflectionClassConstant->method('getName')->willReturn('SOME_CONSTANT');
        $reflectionClassConstant->method('getDeclaringClass')->willReturn($reflectionClass3);

        return [
            [$reflectionClass, FQSEN\Class_::class, '\Tests\Fixtures\FullyQualifiedStructuralElementName\\SomeClass'],
            [$reflectionMethod, FQSEN\Method::class, '\Tests\Fixtures\FullyQualifiedStructuralElementName\\SomeClass::someMethod()'],
            [$reflectionProperty, FQSEN\Property::class, '\Tests\Fixtures\FullyQualifiedStructuralElementName\\SomeClass::$someProperty'],
            [$reflectionClassConstant, FQSEN\ClassConstant::class, '\Tests\Fixtures\FullyQualifiedStructuralElementName\\SomeClass::SOME_CONSTANT'],
        ];
    }

    /**
     * @param $reflection
     * @param string $expectedClass
     * @param string $expectedString
     * @dataProvider provideCreateFromReflection
     */
    public function testCreateFromReflection($reflection, string $expectedClass, string $expectedString)
    {
        $fqsen = FQSEN::createFromReflection($reflection);

        $this->assertInstanceOf($expectedClass, $fqsen);
        $this->assertSame($expectedString, $fqsen->toString());
    }
}

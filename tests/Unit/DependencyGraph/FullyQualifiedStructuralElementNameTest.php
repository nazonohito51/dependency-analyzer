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

    public function provideInclude()
    {
        return [
            'class1' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass', true],
            'class2' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeNamespace\SomeClass', true],
            'class3' => ['\Tests\Fixtures\SomeClass', false],
            'property1' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass::$propertyName', true],
            'property2' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeNamespace\SomeClass::$propertyName', true],
            'property3' => ['\Tests\Fixtures\SomeClass1::$propertyName', false],
            'method1' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass1::methodName()', true],
            'method2' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeNamespace\SomeClass1::methodName()', true],
            'method3' => ['\Tests\Fixtures\SomeClass1::methodName()', false],
            'class_constant1' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass1::SOME_CONSTANT', true],
            'class_constant2' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeNamespace\SomeClass1::SOME_CONSTANT', true],
            'class_constant3' => ['\Tests\Fixtures\SomeClass1::SOME_CONSTANT', false],
            'namespace1' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\Some\\', true],
            'namespace2' => ['\Tests\Fixtures\FullyQualifiedStructuralElementName\Some\Namespace\\', true],
            'namespace3' => ['\Tests\Fixtures\OtherNamespace\\', false]
        ];
    }

    /**
     * @param string $targetElement
     * @param bool $expected
     * @dataProvider provideInclude
     */
    public function testInclude(string $targetElement, bool $expected)
    {
        $namespace = FQSEN::createNamespace('\Tests\Fixtures\FullyQualifiedStructuralElementName');
        $target = FQSEN::createFromString($targetElement);

        $this->assertSame($expected, $namespace->include($target));
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName as FQSEN;
use Tests\TestCase;

class FullyQualifiedStructuralElementNameTest extends TestCase
{
    public function testCreateClass()
    {
        $fqsen = FQSEN::createClass('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass');

        $this->assertSame('\Tests\Fixtures\FullyQualifiedStructuralElementName\SomeClass', $fqsen->toString());
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
        $this->assertFalse($fqsen->isClass());
        $this->assertFalse($fqsen->isMethod());
        $this->assertFalse($fqsen->isProperty());
        $this->assertFalse($fqsen->isClassConstant());
        $this->assertFalse($fqsen->isInterface());
        $this->assertFalse($fqsen->isTrait());
        $this->assertFalse($fqsen->isFunction());
        $this->assertTrue($fqsen->isConstant());
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Inspector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\Inspector\RuleViolationDetector\Component;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher;
use Tests\TestCase;

class ComponentTest extends TestCase
{
    public function testGetName()
    {
        $pattern = $this->createMock(StructuralElementPatternMatcher::class);
        $component = new Component('componentName', $pattern);

        $this->assertSame('componentName', $component->getName());
    }

    public function provideIsBelongedTo()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    /**
     * @param bool $return
     * @param bool $expected
     * @dataProvider provideIsBelongedTo
     */
    public function testIsBelongedTo(bool $return, bool $expected)
    {
        $className = 'className';
        $pattern = $this->createMock(StructuralElementPatternMatcher::class);
        $pattern->method('isMatch')->with($className)->willReturn($return);
        $component = new Component('componentName', $pattern);

        $this->assertSame($expected, $component->isBelongedTo($className));
    }

    public function provideVerifyDepender()
    {
        return [
            [true, false, false, true],
            [false, true, true, true],
            [false, false, true, false],
            [false, true, false, false],
        ];
    }

    /**
     * @param bool $matchSameComponent
     * @param bool $matchDependerPattern
     * @param bool $matchPublicPattern
     * @param bool $expected
     * @dataProvider provideVerifyDepender
     */
    public function testVerifyDepender(bool $matchSameComponent, bool $matchDependerPattern, bool $matchPublicPattern, bool $expected)
    {
        $depender = FullyQualifiedStructuralElementName::createClass('someClass1');
        $dependee = FullyQualifiedStructuralElementName::createMethod('someClass2', 'someMethod');
        $componentPattern = $this->createMock(StructuralElementPatternMatcher::class);
        $componentPattern->method('isMatch')->with($depender->toString())->willReturn($matchSameComponent);
        $dependerPattern = $this->createMock(StructuralElementPatternMatcher::class);
        $dependerPattern->method('isMatchWithFQSEN')->with($depender)->willReturn($matchDependerPattern);
        $publicPattern = $this->createMock(StructuralElementPatternMatcher::class);
        $publicPattern->method('isMatchWithFQSEN')->with($dependee)->willReturn($matchPublicPattern);
        $component = new Component('componentName', $componentPattern, $dependerPattern, null, $publicPattern);

        $this->assertSame($expected, $component->verifyDepender($depender, $dependee));
    }

    public function provideVerifyDependee()
    {
        return [
            [true, false, true],
            [false, true, true],
            [false, false, false],
        ];
    }

    /**
     * @param bool $matchSameComponent
     * @param bool $matchDependeePattern
     * @param bool $expected
     * @dataProvider provideVerifyDependee
     */
    public function testVerifyDependee(bool $matchSameComponent, bool $matchDependeePattern, bool $expected)
    {
        $className = 'className';
        $componentPattern = $this->createMock(StructuralElementPatternMatcher::class);
        $componentPattern->method('isMatch')->with($className)->willReturn($matchSameComponent);
        $dependeePattern = $this->createMock(StructuralElementPatternMatcher::class);
        $dependeePattern->method('isMatch')->with($className)->willReturn($matchDependeePattern);
        $component = new Component('componentName', $componentPattern, null, $dependeePattern);

        $this->assertSame($expected, $component->verifyDependee($className));
    }
}

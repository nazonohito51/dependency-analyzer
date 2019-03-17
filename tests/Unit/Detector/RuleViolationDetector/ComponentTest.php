<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\Detector\RuleViolationDetector\Component;
use DependencyAnalyzer\Patterns\QualifiedNamePattern;
use Tests\TestCase;

class ComponentTest extends TestCase
{
    public function testGetName()
    {
        $pattern = $this->createMock(QualifiedNamePattern::class);
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
        $pattern = $this->createMock(QualifiedNamePattern::class);
        $pattern->method('isMatch')->with($className)->willReturn($return);
        $component = new Component('componentName', $pattern);

        $this->assertSame($expected, $component->isBelongedTo($className));
    }

    public function provideVerifyDepender()
    {
        return [
            [true, false, true],
            [false, true, true],
            [false, false, false],
        ];
    }

    /**
     * @param bool $matchSameComponent
     * @param bool $matchDependerPattern
     * @param bool $expected
     * @dataProvider provideVerifyDepender
     */
    public function testVerifyDepender(bool $matchSameComponent, bool $matchDependerPattern, bool $expected)
    {
        $className = 'className';
        $componentPattern = $this->createMock(QualifiedNamePattern::class);
        $componentPattern->method('isMatch')->with($className)->willReturn($matchSameComponent);
        $dependerPatterns = $this->createMock(QualifiedNamePattern::class);
        $dependerPatterns->method('isMatch')->with($className)->willReturn($matchDependerPattern);
        $component = new Component('componentName', $componentPattern, [$dependerPatterns]);

        $this->assertSame($expected, $component->verifyDepender($className));
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
        $componentPattern = $this->createMock(QualifiedNamePattern::class);
        $componentPattern->method('isMatch')->with($className)->willReturn($matchSameComponent);
        $dependeePattern = $this->createMock(QualifiedNamePattern::class);
        $dependeePattern->method('isMatch')->with($className)->willReturn($matchDependeePattern);
        $component = new Component('componentName', $componentPattern, [], [$dependeePattern]);

        $this->assertSame($expected, $component->verifyDependee($className));
    }
}

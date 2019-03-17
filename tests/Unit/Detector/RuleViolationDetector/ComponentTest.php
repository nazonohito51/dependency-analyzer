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
            [true, true],
            [false, false],
        ];
    }

    /**
     * @param bool $return
     * @param bool $expected
     * @dataProvider provideVerifyDepender
     */
    public function testVerifyDepender(bool $return, bool $expected)
    {
        $className = 'className';
        $qualifiedNamePattern = $this->createMock(QualifiedNamePattern::class);
        $qualifiedNamePattern->method('isMatch')->with($className)->willReturn($return);
        $component = new Component(
            'componentName',
            $this->createMock(QualifiedNamePattern::class),
            [$qualifiedNamePattern]
        );

        $this->assertSame($expected, $component->verifyDepender($className));
    }

    public function provideVerifyDependee()
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * @param bool $return
     * @param bool $expected
     * @dataProvider provideVerifyDependee
     */
    public function testVerifyDependee(bool $return, bool $expected)
    {
        $className = 'className';
        $qualifiedNamePattern = $this->createMock(QualifiedNamePattern::class);
        $qualifiedNamePattern->method('isMatch')->with($className)->willReturn($return);
        $component = new Component(
            'componentName',
            $this->createMock(QualifiedNamePattern::class),
            [],
            [$qualifiedNamePattern]
        );

        $this->assertSame($expected, $component->verifyDependee($className));
    }
}

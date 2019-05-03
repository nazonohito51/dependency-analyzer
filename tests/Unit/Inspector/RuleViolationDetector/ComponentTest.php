<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Inspector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;
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

    public function provideVerifyDepender_WithExtraPattern()
    {
        $depender = FullyQualifiedStructuralElementName::createClass('\Tests\Component\DependerClass');
        $dependerMethod = $depender->createMethodFQSEN('someMethod');
        $dependerProperty = $depender->createPropertyFQSEN('someProperty');
        $dependerConstant = $depender->createClassConstantFQSEN('SOME_CONSTANT');
        $dependee = FullyQualifiedStructuralElementName::createClass('\Tests\Fixtures\DependeeClass');
        $dependeeMethod = $dependee->createMethodFQSEN('someMethod');
        $dependeeProperty = $dependee->createPropertyFQSEN('someProperty');
        $dependeeConstant = $dependee->createClassConstantFQSEN('SOME_CONSTANT');
        $otherDepender = FullyQualifiedStructuralElementName::createClass('\Tests\Fixtures\OtherDependerClass');
        $otherDependerMethod = $otherDepender->createMethodFQSEN('someMethod');
        $otherDependerProperty = $otherDepender->createPropertyFQSEN('someProperty');
        $otherDependerConstant = $otherDepender->createClassConstantFQSEN('SOME_CONSTANT');
        $extraRules = [
            'class and class' => [
                '\Tests\Fixtures\DependeeClass' => new StructuralElementPatternMatcher(['\Tests\Component\DependerClass'])
            ],
            'method and class' => [
                '\Tests\Fixtures\DependeeClass::someMethod()' => new StructuralElementPatternMatcher(['\Tests\Component\DependerClass'])
            ],
            'class and exclude class' => [
                '\Tests\Fixtures\DependeeClass' => new StructuralElementPatternMatcher(['!\Tests\Component\DependerClass'])
            ],
            'class and exclude class element' => [
                '\Tests\Fixtures\DependeeClass' => new StructuralElementPatternMatcher(['!\Tests\Component\DependerClass::someMethod()'])
            ],
            'have multi pattern' => [
                '\Tests\Fixtures\DependeeClass' => new StructuralElementPatternMatcher(['\Tests\Component\DependerClass::someMethod()', '\Tests\Component\DependerClass::$someProperty'])
            ]
        ];

        return [
            'depend on class 1' => [$extraRules['class and class'], $depender, $dependee, true],
            'depend on class 2' => [$extraRules['class and class'], $dependerMethod, $dependee, true],
            'depend on class 3' => [$extraRules['class and class'], $dependerProperty, $dependee, true],
            'depend on class 4' => [$extraRules['class and class'], $dependerConstant, $dependee, true],
            'depend on class 5' => [$extraRules['class and class'], $otherDepender, $dependee, false],
            'depend on class 6' => [$extraRules['class and class'], $otherDependerMethod, $dependee, false],
            'depend on class 7' => [$extraRules['class and class'], $otherDependerProperty, $dependee, false],
            'depend on class 8' => [$extraRules['class and class'], $otherDependerConstant, $dependee, false],
            'depend on class element 1' => [$extraRules['class and class'], $depender, $dependee, true],
            'depend on class element 2' => [$extraRules['class and class'], $depender, $dependeeMethod, true],
            'depend on class element 3' => [$extraRules['class and class'], $depender, $dependeeProperty, true],
            'depend on class element 4' => [$extraRules['class and class'], $depender, $dependeeConstant, true],
            'depend on class element 5' => [$extraRules['class and class'], $otherDepender, $dependee, false],
            'depend on class element 6' => [$extraRules['class and class'], $otherDepender, $dependeeMethod, false],
            'depend on class element 7' => [$extraRules['class and class'], $otherDepender, $dependeeProperty, false],
            'depend on class element 8' => [$extraRules['class and class'], $otherDepender, $dependeeConstant, false],
            'restrict class element 1' => [$extraRules['method and class'], $depender, $dependeeMethod, true],
            'restrict class element 2' => [$extraRules['method and class'], $dependerMethod, $dependeeMethod, true],
            'restrict class element 3' => [$extraRules['method and class'], $dependerProperty, $dependeeMethod, true],
            'restrict class element 4' => [$extraRules['method and class'], $dependerConstant, $dependeeMethod, true],
            'restrict class element 5' => [$extraRules['method and class'], $depender, $dependee, false],
            'restrict class element 6' => [$extraRules['method and class'], $depender, $dependeeProperty, false],
            'restrict class element 7' => [$extraRules['method and class'], $depender, $dependeeConstant, false],
            'exclude class 1' => [$extraRules['class and exclude class'], $depender, $dependee, false],
            'exclude class 2' => [$extraRules['class and exclude class'], $dependerMethod, $dependee, false],
            'exclude class 3' => [$extraRules['class and exclude class'], $dependerProperty, $dependee, false],
            'exclude class 4' => [$extraRules['class and exclude class'], $dependerConstant, $dependee, false],
            'exclude class 5' => [$extraRules['class and exclude class'], $otherDepender, $dependee, true],
            'exclude class 6' => [$extraRules['class and exclude class'], $otherDependerMethod, $dependee, true],
            'exclude class 7' => [$extraRules['class and exclude class'], $otherDependerProperty, $dependee, true],
            'exclude class 8' => [$extraRules['class and exclude class'], $otherDependerConstant, $dependee, true],
            'exclude class element 1' => [$extraRules['class and exclude class element'], $depender, $dependee, true],
            'exclude class element 2' => [$extraRules['class and exclude class element'], $dependerMethod, $dependee, false],
            'exclude class element 3' => [$extraRules['class and exclude class element'], $dependerProperty, $dependee, true],
            'exclude class element 4' => [$extraRules['class and exclude class element'], $dependerConstant, $dependee, true],
            'multi pattern 1' => [$extraRules['have multi pattern'], $depender, $dependee, false],
            'multi pattern 2' => [$extraRules['have multi pattern'], $dependerMethod, $dependee, true],
            'multi pattern 3' => [$extraRules['have multi pattern'], $dependerProperty, $dependee, true],
            'multi pattern 4' => [$extraRules['have multi pattern'], $dependerConstant, $dependee, false],
        ];
    }

    /**
     * @param array $extraPatterns
     * @param FQSEN $depender
     * @param FQSEN $dependee
     * @param bool $expected
     * @dataProvider provideVerifyDepender_WithExtraPattern
     */
    public function testVerifyDepender_WithExtraPattern(array $extraPatterns, FQSEN $depender, FQSEN $dependee, bool $expected)
    {
        $componentPattern = new StructuralElementPatternMatcher(['\Tests\Fixtures\DependeeClass']);
        $publicPattern = new StructuralElementPatternMatcher(['!\\']);
        $component = new Component('componentName', $componentPattern, null, null, $publicPattern, $extraPatterns);

        $this->assertSame($expected, $component->verifyDepender($depender, $dependee));
    }
}

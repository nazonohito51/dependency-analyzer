<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher;
use Tests\TestCase;

class StructuralElementPatternMatcherTest extends TestCase
{
    public function tearDown()
    {
        StructuralElementPatternMatcher::setPhpNativeClasses([]);
    }

    public function provideVerifyPattern_WhenValidPattern()
    {
        return [
            [['\\Tests']],
            [['\\Tests\\Fixtures']],
            [['\\Tests\\Fixtures\\']],
            [['!\\Tests']],
            [['!\\Tests\\Fixtures']],
            [['!\\Tests\\Fixtures\\']],
            [['\\']],
            [['@php_native']]
        ];
    }

    /**
     * @param array $patterns
     * @dataProvider provideVerifyPattern_WhenValidPattern
     */
    public function testVerifyPattern_WhenValidPattern(array $patterns)
    {
        $qualifiedName = new StructuralElementPatternMatcher($patterns);

        $this->assertInstanceOf(StructuralElementPatternMatcher::class, $qualifiedName);
    }

    public function provideVerifyPattern_WhenInvalidPattern()
    {
        return [
            [['Tests']],
            [['Tests\\Fixtures']],
            [['!!\\Tests']],
            [['?\\Tests']],
            [['@non_exist_magic_word']]
        ];
    }

    /**
     * @param array $patterns
     * @expectedException \DependencyAnalyzer\Exceptions\InvalidQualifiedNamePatternException
     * @dataProvider provideVerifyPattern_WhenInvalidPattern
     */
    public function testVerifyPattern_WhenInvalidPattern(array $patterns)
    {
        new StructuralElementPatternMatcher($patterns);
    }

    public function provideIsMatch()
    {
        return [
            'pattern and target is same 1' => [['\Tests'], '\Tests', true],
            'pattern and target is same 2' => [['\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass', true],
            'pattern and target is same 3' => [['\Tests\Fixtures\SomeClass::$someProperty'], '\Tests\Fixtures\SomeClass::$someProperty', true],
            'pattern and target is same 4' => [['\Tests\Fixtures\SomeClass::someMethod()'], '\Tests\Fixtures\SomeClass::someMethod()', true],
            'pattern and target is same 5' => [['\Tests\Fixtures\SomeClass::SOME_CONSTANT'], '\Tests\Fixtures\SomeClass::SOME_CONSTANT', true],
            'pattern and target is same 6' => [['\Tests\Fixtures\SomeClass::$someProperty'], '\Tests\Fixtures\SomeClass::someMethod()', false],
            'pattern and target is same 7' => [['\Tests\Fixtures\SomeClass::someMethod()'], '\Tests\Fixtures\SomeClass::$someProperty', false],
            'pattern include target 1' => [['\Tests\Fixtures'], '\Tests\Fixtures\SomeClass', false],
            'pattern include target 2' => [['\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass', true],
            'pattern include target 3' => [['\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass::$someProperty', true],
            'pattern include target 4' => [['\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass::someMethod()', true],
            'pattern include target 5' => [['\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass::SOME_CONSTANT', true],
            'pattern include target 6' => [['\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass', true],
            'pattern include target 7' => [['\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass::$someProperty', true],
            'pattern include target 8' => [['\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass::someMethod()', true],
            'pattern include target 9' => [['\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass::SOME_CONSTANT', true],
            'pattern include target 10' => [['\Tests\Fixtures\SomeClass::$someProperty'], '\Tests\Fixtures\SomeClass', false],
            'pattern include target 11' => [['\Tests\Fixtures\SomeClass::$someProperty'], '\Tests\Fixtures\SomeClass::$someProperty', true],
            'pattern include target 12' => [['\Tests\Fixtures\SomeClass::$someProperty'], '\Tests\Fixtures\SomeClass::someMethod()', false],
            'pattern include target 13' => [['\Tests\Fixtures\SomeClass::$someProperty'], '\Tests\Fixtures\SomeClass::SOME_CONSTANT', false],
            'all namespace 1' => [['\\'], '\Tests', true],
            'all namespace 2' => [['\\'], '\Tests\Fixtures\SomeClass', true],
            'all namespace 3' => [['\\'], '\Tests\Fixtures\SomeClass::$someProperty', true],
            'all namespace 4' => [['\\'], '\Tests\Fixtures\SomeClass::someMethod()', true],
            'all namespace 5' => [['\\'], '\Tests\Fixtures\SomeClass::SOME_CONSTANT', true],
            'multi patterns 1' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Fixtures', true],
            'multi patterns 2' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Fixtures\SomeClass', true],
            'multi patterns 3' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Component\SomeClass', true],
            'multi patterns 4' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Component\SomeClass::$someProperty', true],
            'multi patterns 5' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Component\SomeClass::someMethod()', true],
            'multi patterns 6' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Component\SomeClass::SOME_CONSTANT', true],
            'multi patterns 7' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Unit\SomeClass', false],
            'pattern with exclude pattern 1' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass', false],
            'pattern with exclude pattern 2' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass::$someProperty', false],
            'pattern with exclude pattern 3' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass::someMethod()', false],
            'pattern with exclude pattern 4' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass::SOME_CONSTANT', false],
            'pattern with exclude pattern 5' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Component\SomeClass', true],
            'pattern with exclude pattern 6' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Component\SomeClass::$someProperty', true],
            'pattern with exclude pattern 7' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Component\SomeClass::someMethod()', true],
            'pattern with exclude pattern 8' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Component\SomeClass::SOME_CONSTANT', true],
            'have only exclude pattern 1' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass', false],
            'have only exclude pattern 2' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass::$someProperty', false],
            'have only exclude pattern 3' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass::someMethod()', false],
            'have only exclude pattern 4' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass::SOME_CONSTANT', false],
            'have only exclude pattern 5' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Component\SomeClass', true],
            'have only exclude pattern 6' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Component\SomeClass::$someProperty', true],
            'have only exclude pattern 7' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Component\SomeClass::someMethod()', true],
            'have only exclude pattern 8' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Component\SomeClass::SOME_CONSTANT', true],
            'have only exclude pattern 9' => [['!\Tests\Fixtures\SomeClass'], '\Tests', true],
            'have only exclude pattern 10' => [['!\\'], '\Tests', false],
            'incomplete pattern match' => [['\Tests\Inte'], '\Tests\Component', false],
            'magic word' => [[StructuralElementPatternMatcher::PHP_NATIVE_CLASSES], '\SplFileObject', true],
            'exclude magic word' => [['!' . StructuralElementPatternMatcher::PHP_NATIVE_CLASSES], '\SplFileObject', false]
        ];
    }

    /**
     * @param array $patterns
     * @param string $className
     * @param bool $expected
     * @dataProvider provideIsMatch
     */
    public function testIsMatch(array $patterns, string $className, bool $expected)
    {
        StructuralElementPatternMatcher::setPhpNativeClasses(['SplFileObject']);
        $classNameMatcher = new StructuralElementPatternMatcher($patterns);

        $this->assertSame($expected, $classNameMatcher->isMatch($className));
    }
}

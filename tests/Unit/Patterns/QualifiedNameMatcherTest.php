<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Matcher;

use DependencyAnalyzer\DependencyGraph\ClassNameMatcher;
use Tests\TestCase;

class QualifiedNameMatcherTest extends TestCase
{
    public function tearDown()
    {
        ClassNameMatcher::setPhpNativeClasses([]);
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
        $qualifiedName = new ClassNameMatcher($patterns);

        $this->assertInstanceOf(ClassNameMatcher::class, $qualifiedName);
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
        $qualifiedName = new ClassNameMatcher($patterns);

        $this->assertInstanceOf(ClassNameMatcher::class, $qualifiedName);
    }

    public function provideIsMatch()
    {
        return [
            'pattern and className is same 1' => [['\\Tests'], 'Tests', true],
            'pattern and className is same 2' => [['\\Tests\\Fixtures\\SomeClass'], 'Tests\\Fixtures\\SomeClass', true],
            'pattern include className 1' => [['\\Tests\\Fixtures'], 'Tests\\Fixtures\\SomeClass', false],
            'pattern include className 2' => [['\\Tests\\Fixtures\\'], 'Tests\\Fixtures\\SomeClass', true],
            'pattern include className 3' => [['\\'], 'Tests', true],
            'pattern include className 4' => [['\\'], 'Tests\\Fixtures\\SomeClass', true],
            'multi patterns 1' => [['\\Tests\\Fixtures', '\\Tests\\Fixtures\\', '\\Tests\\Component\\'], 'Tests\\Fixtures', true],
            'multi patterns 2' => [['\\Tests\\Fixtures', '\\Tests\\Fixtures\\', '\\Tests\\Component\\'], 'Tests\\Fixtures\\SomeClass', true],
            'multi patterns 3' => [['\\Tests\\Fixtures', '\\Tests\\Fixtures\\', '\\Tests\\Component\\'], 'Tests\\Component\\SomeClass', true],
            'multi patterns 4' => [['\\Tests\\Fixtures', '\\Tests\\Fixtures\\', '\\Tests\\Component\\'], 'Tests\\Unit\\SomeClass', false],
            'pattern with exclude pattern 1' => [['\\Tests\\', '!\\Tests\\Fixtures\\'], 'Tests\\Fixtures\\SomeClass', false],
            'pattern with exclude pattern 2' => [['\\Tests\\', '!\\Tests\\Fixtures\\'], 'Tests\\Component\\SomeClass', true],
            'have only exclude pattern 1' => [['!\\Tests\\Fixtures\\SomeClass'], 'Tests\\Fixtures\\SomeClass', false],
            'have only exclude pattern 2' => [['!\\Tests\\Fixtures\\SomeClass'], 'Tests\\Component\\SomeClass', true],
            'have only exclude pattern 3' => [['!\\Tests\\Fixtures\\SomeClass'], 'Tests', true],
            'have only exclude pattern 4' => [['!\\'], 'Tests', false],
            'incomplete pattern match' => [['\\Tests\\Inte'], 'Tests\\Component', false],
            'magic word' => [[ClassNameMatcher::PHP_NATIVE_CLASSES], 'SplFileObject', true],
            'exclude magic word' => [['!' . ClassNameMatcher::PHP_NATIVE_CLASSES], 'SplFileObject', false]
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
        ClassNameMatcher::setPhpNativeClasses(['SplFileObject']);
        $qualifiedName = new ClassNameMatcher($patterns);

        $actual = $qualifiedName->isMatch($className);

        $this->assertSame($expected, $actual);
    }
}

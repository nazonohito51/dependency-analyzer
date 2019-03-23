<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Patterns;

use DependencyAnalyzer\Patterns\QualifiedNamePattern;
use Tests\TestCase;

class QualifiedNamePatternTest extends TestCase
{
    public function tearDown()
    {
        QualifiedNamePattern::setPhpNativeClasses([]);
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
        $qualifiedName = new QualifiedNamePattern($patterns);

        $this->assertInstanceOf(QualifiedNamePattern::class, $qualifiedName);
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
        $qualifiedName = new QualifiedNamePattern($patterns);

        $this->assertInstanceOf(QualifiedNamePattern::class, $qualifiedName);
    }

    public function provideIsMatch()
    {
        return [
            'pattern and className is same 1' => [['\\Tests'], 'Tests', true],
            'pattern and className is same 2' => [['\\Tests\\Fixtures\\SomeClass'], 'Tests\\Fixtures\\SomeClass', true],
            'pattern include className 1' => [['\\Tests\\Fixtures'], 'Tests\\Fixtures\\SomeClass', true],
            'pattern include className 2' => [['\\Tests\\Fixtures\\'], 'Tests\\Fixtures\\SomeClass', true],
            'pattern include className 3' => [['\\'], 'Tests\\Fixtures\\SomeClass', true],
            'multi patterns 1' => [['\\Tests\\Fixtures', '\\Tests\\Integration'], 'Tests\\Fixtures\\SomeClass', true],
            'multi patterns 2' => [['\\Tests\\Fixtures', '\\Tests\\Integration'], 'Tests\\Integration\\SomeClass', true],
            'multi patterns 3' => [['\\Tests\\Fixtures', '\\Tests\\Integration'], 'Tests\\Unit\\SomeClass', false],
            'pattern with exclude pattern 1' => [['\\Tests', '!\\Tests\\Fixtures'], 'Tests\\Fixtures\\SomeClass', false],
            'pattern with exclude pattern 2' => [['\\Tests', '!\\Tests\\Fixtures'], 'Tests\\Integration\\SomeClass', true],
            'incomplete patten match' => [['\\Tests\\Inte'], 'Tests\\Integration\\SomeClass', false],
            'magic word' => [[QualifiedNamePattern::PHP_NATIVE_CLASSES], 'SplFileObject', true],
            'exclude magic word' => [['!' . QualifiedNamePattern::PHP_NATIVE_CLASSES], 'SplFileObject', false]
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
        QualifiedNamePattern::setPhpNativeClasses(['SplFileObject']);
        $qualifiedName = new QualifiedNamePattern($patterns);

        $actual = $qualifiedName->isMatch($className);

        $this->assertSame($expected, $actual);
    }
}

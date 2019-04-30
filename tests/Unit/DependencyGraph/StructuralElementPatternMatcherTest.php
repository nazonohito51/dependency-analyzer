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
            'pattern include target 1' => [['\Tests\Fixtures'], '\Tests\Fixtures\SomeClass', false],
            'pattern include target 2' => [['\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass', true],
            'pattern include target 3' => [['\\'], '\Tests', true],
            'pattern include target 4' => [['\\'], '\Tests\Fixtures\SomeClass', true],
            'multi patterns 1' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Fixtures', true],
            'multi patterns 2' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Fixtures\SomeClass', true],
            'multi patterns 3' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Component\SomeClass', true],
            'multi patterns 4' => [['\Tests\Fixtures', '\Tests\Fixtures\\', '\Tests\Component\\'], '\Tests\Unit\SomeClass', false],
            'pattern with exclude pattern 1' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Fixtures\SomeClass', false],
            'pattern with exclude pattern 2' => [['\Tests\\', '!\Tests\Fixtures\\'], '\Tests\Component\SomeClass', true],
            'have only exclude pattern 1' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Fixtures\SomeClass', false],
            'have only exclude pattern 2' => [['!\Tests\Fixtures\SomeClass'], '\Tests\Component\SomeClass', true],
            'have only exclude pattern 3' => [['!\Tests\Fixtures\SomeClass'], '\Tests', true],
            'have only exclude pattern 4' => [['!\\'], '\Tests', false],
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

//    public function provideIsMatchWithFQSEN()
//    {
//        return [
//            'namespace with class' => [],
//            'namespace with method' => [],
//            'namespace with property' => [],
//            'namespace with class constant' => [],
//            'class with class' => [],
//            'class with method' => [],
//            'class with property' => [],
//            'class with class constant' => [],
//            'method with class' => [],
//            'method with method' => [],
//            'method with property' => [],
//            'method with class constant' => [],
//            'property with class' => [],
//            'property with method' => [],
//            'property with property' => [],
//            'property with class constant' => [],
//            'class constant with class' => [],
//            'class constant with method' => [],
//            'class constant with property' => [],
//            'class constant with class constant' => [],
//            'multiple patterns' => [],
//        ];
//    }
//
//    public function testIsMatchWithFQSEN(FullyQualifiedStructuralElementName\Base $target, array $patterns, bool $expected)
//    {
//        $classNameMatcher = new StructuralElementPatternMatcher(['\Tests\Fixtures\\']);
//
//        $this->assertSame($expected, $classNameMatcher->isMatchWithFQSEN($target));
//    }
}

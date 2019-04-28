<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Matcher;

use DependencyAnalyzer\DependencyGraph\ClassNameMatcher;
use Tests\TestCase;

class ClassNameMatcherTest extends TestCase
{
    public function provideIsMatch()
    {
        return [
            'class1' => [['\Tests\Fixtures\ClassMatcher\SomeClass1'], 'Tests\Fixtures\ClassMatcher\SomeClass1', true],
            'class2' => [['\Tests\Fixtures\ClassMatcher\SomeClass1'], 'Tests\Fixtures\ClassMatcher\SomeClass2', false],
            'namespace1' => [['\Tests\Fixtures\ClassMatcher\\'], 'Tests\Fixtures\ClassMatcher\SomeClass1', true],
            'namespace2' => [['\Tests\Fixtures\ClassMatcher\\'], 'Tests\Fixtures\DifferentNameSpace\SomeClass1', false],
            'exclude class1' => [['!\Tests\Fixtures\ClassMatcher\SomeClass1'], 'Tests\Fixtures\ClassMatcher\SomeClass1', false],
            'exclude class2' => [['!\Tests\Fixtures\ClassMatcher\SomeClass1'], 'Tests\Fixtures\ClassMatcher\SomeClass2', true],
            'exclude namespace1' => [['!\Tests\Fixtures\ClassMatcher\\'], 'Tests\Fixtures\ClassMatcher\SomeClass1', false],
            'exclude namespace2' => [['!\Tests\Fixtures\ClassMatcher\\'], 'Tests\Fixtures\DifferentNameSpace\SomeClass1', true],
            'have multi pattern1' => [['\Tests\Fixtures\ClassMatcher\SomeClass1', '\Tests\Fixtures\ClassMatcher\SomeClass2'], 'Tests\Fixtures\ClassMatcher\SomeClass1', true],
            'have multi pattern2' => [['\Tests\Fixtures\ClassMatcher\SomeClass1', '\Tests\Fixtures\ClassMatcher\SomeClass2'], 'Tests\Fixtures\ClassMatcher\SomeClass2', true],
            'have multi pattern3' => [['\Tests\Fixtures\ClassMatcher\SomeClass1', '\Tests\Fixtures\ClassMatcher\SomeClass2'], 'Tests\Fixtures\ClassMatcher\SomeClass3', false],
            'have multi pattern4' => [['\Tests\Fixtures\ClassMatcher\\', '!\Tests\Fixtures\ClassMatcher\SomeClass2'], 'Tests\Fixtures\ClassMatcher\SomeClass1', true],
            'have multi pattern5' => [['\Tests\Fixtures\ClassMatcher\\', '!\Tests\Fixtures\ClassMatcher\SomeClass2'], 'Tests\Fixtures\ClassMatcher\SomeClass2', false],
        ];
    }

    /**
     * @param array $patterns
     * @param string $target
     * @param bool $expected
     * @dataProvider provideIsMatch
     */
    public function testIsMatch(array $patterns, string $target, bool $expected)
    {
        $matcher = new ClassNameMatcher($patterns);

        $this->assertSame($expected, $matcher->isMatch($target));
    }
}

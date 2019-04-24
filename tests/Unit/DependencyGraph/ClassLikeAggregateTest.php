<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\ClassLike;
use DependencyAnalyzer\DependencyGraph\ClassLikeAggregate;
use PHPStan\Reflection\ClassReflection;
use Tests\TestCase;

class ClassLikeAggregateTest extends TestCase
{
    public function testAggregate()
    {
        $classLike = $this->createClassLike('someName');
        $classLikeAggregate = new ClassLikeAggregate([$classLike]);

        $this->assertCount(1, $classLikeAggregate);
        $this->assertTrue($classLikeAggregate->haveClassLike('someName'));
        $this->assertSame($classLike, $classLikeAggregate->getClassLike('someName'));
    }

    public function testMerge()
    {
        $classReflection = $this->createClassReflection();
        $classLike1 = $this->createClassLike('someName1');
        $classLike2 = $this->createClassLike('someName2');
        $classLike2->expects($this->once())->method('getDependees')->willReturn([$classReflection]);
        $classLike2->expects($this->once())->method('addDependee')->with($classReflection);
        $classLike3 = $this->createClassLike('someName3');
        $classLikeAggregate1 = new ClassLikeAggregate([$classLike1, $classLike2]);
        $classLikeAggregate2 = new ClassLikeAggregate([$classLike2, $classLike3]);

        $classLikeAggregate1->merge($classLikeAggregate2);

        $this->assertCount(3, $classLikeAggregate1);
        $this->assertSame($classLike1, $classLikeAggregate1->getClassLike('someName1'));
        $this->assertSame($classLike2, $classLikeAggregate1->getClassLike('someName2'));
        $this->assertSame($classLike3, $classLikeAggregate1->getClassLike('someName3'));
    }

    protected function createClassLike(string $name)
    {
        $classLike = $this->createMock(ClassLike::class);
        $classLike->method('getName')->willReturn($name);

        return $classLike;
    }

    protected function createClassReflection()
    {
        return $this->createMock(ClassReflection::class);
    }
}

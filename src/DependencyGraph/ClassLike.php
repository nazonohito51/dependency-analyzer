<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use PHPStan\Reflection\ClassReflection;

/**
 * Something can have dependency, likely class, interface, trait
 */
class ClassLike
{
    /**
     * @var ClassReflection
     */
    protected $classReflection;

    /**
     * @var ClassReflection[]
     */
    protected $dependeeReflections = [];

    public function __construct(ClassReflection $classReflection)
    {
        $this->classReflection = $classReflection;
    }

    public function getName(): string
    {
        return $this->classReflection->getDisplayName();
    }

    public function getReflection(): ClassReflection
    {
        return $this->classReflection;
    }

    /**
     * @return ClassReflection[]
     */
    public function getDependees(): array
    {
        return $this->dependeeReflections;
    }

    public function addDependee(ClassReflection $classReflection): void
    {
        foreach ($this->dependeeReflections as $dependeeReflection) {
            if ($dependeeReflection->getDisplayName() === $classReflection->getDisplayName()) {
                return;
            }
        }
        $this->dependeeReflections[] = $classReflection;
    }

    public function toArray(): array
    {
        $ret = [];
        foreach ($this->getDependees() as $dependee) {
            $ret[$this->getName()][] = $dependee->getDisplayName();
        }

        return $ret;
    }
}

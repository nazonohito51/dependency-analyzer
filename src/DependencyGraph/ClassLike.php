<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use Fhaculty\Graph\Vertex;
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
    protected $dependeeReflection;

    public function __construct(ClassReflection $classReflection)
    {
        $this->classReflection = $classReflection;
    }

    public function getName(): string
    {
        return $this->classReflection->getDisplayName();
    }

    public function addDependOn(ClassReflection $classReflection): void
    {
        $this->dependeeReflection[] = $classReflection;
    }
}

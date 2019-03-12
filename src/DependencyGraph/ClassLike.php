<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use Fhaculty\Graph\Vertex;

/**
 * Something can have dependency, likely class, interface, trait
 */
class ClassLike extends Vertex
{
    public function addDependOn(ClassLike $classLike)
    {
        return $this->createEdgeTo($classLike);
    }
}

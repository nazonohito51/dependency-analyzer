<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver\DepsInternal;
use DependencyAnalyzer\DependencyGraphBuilder\ExtraPhpDocTagResolver;
use Fhaculty\Graph\Vertex;

class ClassLike
{
    /**
     * @var Vertex
     */
    private $vertex;

    public function __construct(Vertex $vertex)
    {
        $this->vertex = $vertex;
    }

    public function getVertex(): Vertex
    {
        return $this->vertex;
    }

    public function getName(): string
    {
        return $this->vertex->getId();
    }

    /**
     * @return string[]
     */
    public function getCanOnlyUsedByTag(): array
    {
        return $this->vertex->getAttribute(ExtraPhpDocTagResolver::ONLY_USED_BY_TAGS);
    }

    /**
     * @return DepsInternal[]
     */
    public function getDepsInternalTag(): array
    {
        return $this->vertex->getAttribute(ExtraPhpDocTagResolver::DEPS_INTERNAL);
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}

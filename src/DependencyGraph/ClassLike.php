<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver\DepsInternal;
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
     * @return DepsInternal[]
     */
    public function getDepsInternalTag(): array
    {
        return $this->vertex->getAttribute(DepsInternal::TAG_NAME);
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}

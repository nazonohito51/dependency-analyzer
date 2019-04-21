<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class UseTrait extends Base
{
    public function getType(): string
    {
        return DependencyGraph::TYPE_USE_TRAIT;
    }

    public function isEqual(Base $that): bool
    {
        return $this->getType() === $that->getType();
    }

    public function toString(): string
    {
        return $this->getType();
    }
}

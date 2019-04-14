<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class ImplementsClass
{
    public function getType()
    {
        return DependencyGraph::TYPE_IMPLEMENTS;
    }
}

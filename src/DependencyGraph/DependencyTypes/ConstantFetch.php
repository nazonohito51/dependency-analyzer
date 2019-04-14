<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class ConstantFetch
{
    public function getType()
    {
        return DependencyGraph::TYPE_CONSTANT_FETCH;
    }
}

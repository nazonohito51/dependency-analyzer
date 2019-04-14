<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class PropertyFetch
{
    public function getType()
    {
        return DependencyGraph::TYPE_PROPERTY_FETCH;
    }
}

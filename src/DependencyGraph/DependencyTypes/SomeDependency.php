<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class SomeDependency extends Base
{
    public function getType()
    {
        return DependencyGraph::TYPE_SOME_DEPENDENCY;
    }
}

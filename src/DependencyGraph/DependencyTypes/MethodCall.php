<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class MethodCall
{
    public function getType()
    {
        return DependencyGraph::TYPE_METHOD_CALL;
    }
}

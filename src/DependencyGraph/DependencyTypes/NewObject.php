<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class NewObject
{
    /**
     * @var \ReflectionClass
     */
    private $class;
    /**
     * @var string
     */
    private $caller;

    public function __construct(\ReflectionClass $class, string $caller = null)
    {
        $this->class = $class;
        $this->caller = $caller;
    }

    public function getType()
    {
        return DependencyGraph::TYPE_NEW;
    }
}

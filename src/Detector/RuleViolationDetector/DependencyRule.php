<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Patterns\QualifiedNamePattern;
use Fhaculty\Graph\Vertex;

class DependencyRule
{
    // group namespaces
    // check dependency direction

    /**
     * @var array
     */
    protected $definition;

    /**
     * @var Component[]
     */
    protected $components = [];

    public function __construct(array $components)
    {
        $this->components = $components;
    }

    public function isSatisfyBy(DependencyGraph $graph): array
    {
        $errors = [];

        foreach ($graph->getDependencyArrows() as $edge) {
            $depender = $edge->getVertexStart();
            $dependee = $edge->getVertexEnd();

            // TODO: add exclude rule
            if (is_null($this->getGroupName($depender)) || is_null($this->getGroupName($dependee))) {
                continue;
            }

            foreach ($this->components as $component) {
                if ($component->isBelongedTo($dependee->getId())) {
                    if (!$component->verifyDepender($depender->getId())) {
                        $errors[] = "{$depender->getId()}({$this->getGroupName($depender)}) must not depend on {$dependee->getId()}({$this->getGroupName($dependee)}).";
                    }
                }

                if ($component->isBelongedTo($depender->getId())) {
                    if (!$component->verifyDependee($dependee->getId())) {
                        $errors[] = "{$depender->getId()}({$this->getGroupName($depender)}) must not depend on {$dependee->getId()}({$this->getGroupName($dependee)}).";
                    }
                }
            }
        }

        return $errors;
    }

    protected function getGroupName(Vertex $vertex): ?string
    {
        foreach ($this->components as $component) {
            if ($component->isBelongedTo($vertex->getId())) {
                return $component->getName();
            }
        }

        return null;
    }

    public function getDefinition()
    {
        return $this->definition;
    }
}

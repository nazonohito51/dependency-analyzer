<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Inspector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Matcher\ClassNameMatcher;
use DependencyAnalyzer\Inspector\Responses\VerifyDependencyResponse;
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

    /**
     * @var string
     */
    private $ruleName;

    public function __construct(string $ruleName, array $components)
    {
        $this->ruleName = $ruleName;
        $this->components = $components;
    }

    public function getRuleName(): string
    {
        return $this->ruleName;
    }

    public function isSatisfyBy(DependencyGraph $graph): VerifyDependencyResponse
    {
        $response = new VerifyDependencyResponse($this->getRuleName());
//        $errors = [];

        foreach ($graph->getDependencyArrows() as $edge) {
            $depender = $edge->getVertexStart();
            $dependee = $edge->getVertexEnd();

            // TODO: add exclude rule
            if (is_null($this->getComponentName($depender)) || is_null($this->getComponentName($dependee))) {
                continue;
            }

            foreach ($this->components as $component) {
                if ($component->isBelongedTo($dependee->getId())) {
                    if (!$component->verifyDepender($depender->getId())) {
                        $response->addRuleViolation(
                            $this->getComponent($depender)->getName(),
                            $depender->getId(),
                            $this->getComponent($dependee)->getName(),
                            $dependee->getId()
                        );
//                        $errors[] = "{$depender->getId()}({$this->getComponentName($depender)}) must not depend on {$dependee->getId()}({$this->getComponentName($dependee)}).";
                    }
                }

                if ($component->isBelongedTo($depender->getId())) {
                    if (!$component->verifyDependee($dependee->getId())) {
                        $response->addRuleViolation(
                            $this->getComponent($depender)->getName(),
                            $depender->getId(),
                            $this->getComponent($dependee)->getName(),
                            $dependee->getId()
                        );
//                        $errors[] = "{$depender->getId()}({$this->getComponentName($depender)}) must not depend on {$dependee->getId()}({$this->getComponentName($dependee)}).";
                    }
                }
            }
        }

        return $response;
//        return $errors;
    }

    protected function getComponentName(Vertex $vertex): ?string
    {
        return $this->getComponent($vertex) ? $this->getComponent($vertex)->getName() : null;
    }

    protected function getComponent(Vertex $vertex): ?Component
    {
        foreach ($this->components as $component) {
            if ($component->isBelongedTo($vertex->getId())) {
                return $component;
            }
        }

        return null;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function toArray()
    {
        $components = [];
        foreach ($this->components as $component) {
            $components[$component->getName()] = $component->toArray();
        }

        return [$this->getRuleName() => $components];
    }
}

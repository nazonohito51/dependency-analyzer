<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Inspector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher;
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

        foreach ($graph->getDependencyArrows() as $edge) {
            $depender = $edge->getDependerClass();
            $dependee = $edge->getDependeeClass();

            // TODO: add exclude rule
            if (is_null($this->getComponentName($depender)) || is_null($this->getComponentName($dependee))) {
                continue;
            }

            foreach ($this->components as $component) {
                if ($component->isBelongedTo($dependee->toString())) {
                    if (!$component->verifyDepender($depender->toString())) {
                        $response->addRuleViolation(
                            $this->getComponent($depender)->getName(),
                            $depender->toString(),
                            $this->getComponent($dependee)->getName(),
                            $dependee->toString()
                        );
                    }
                }

                if ($component->isBelongedTo($depender->toString())) {
                    if (!$component->verifyDependee($dependee->toString())) {
                        $response->addRuleViolation(
                            $this->getComponent($depender)->getName(),
                            $depender->toString(),
                            $this->getComponent($dependee)->getName(),
                            $dependee->toString()
                        );
                    }
                }
            }
        }

        return $response;
    }

    protected function getComponentName(FQSEN $fqsen): ?string
    {
        return $this->getComponent($fqsen) ? $this->getComponent($fqsen)->getName() : null;
    }

    protected function getComponent(FQSEN $fqsen): ?Component
    {
        foreach ($this->components as $component) {
            if ($component->isBelongedTo($fqsen->toString())) {
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

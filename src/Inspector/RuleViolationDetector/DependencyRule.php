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

    /**
     * @param string $ruleName
     * @param array $components
     * @da-internal \DependencyAnalyzer\Inspector\RuleViolationDetector\DependencyRuleFactory
     */
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

        foreach ($graph->getDependencyArrows() as $dependencyArrow) {
            foreach ($dependencyArrow->getDependencies() as $dependency) {
                /** @var DependencyGraph\FullyQualifiedStructuralElementName\Base $dependerFQSEN */
                $dependerFQSEN = $dependency[0];
                /** @var DependencyGraph\FullyQualifiedStructuralElementName\Base $dependeeFQSEN */
                $dependeeFQSEN = $dependency[1];
//                $depender = $dependencyArrow->getDependerClass();
//                $dependee = $dependencyArrow->getDependeeClass();

                // TODO: add exclude rule
                $dependerComponent = $this->getComponent($dependerFQSEN);
                $dependeeComponent = $this->getComponent($dependeeFQSEN);
                if (is_null($dependerComponent) || is_null($dependeeComponent) || $dependerComponent === $dependeeComponent) {
                    continue;
                }

                foreach ($this->components as $component) {
                    if ($component->isBelongedTo($dependeeFQSEN->toString())) {
                        if (!$component->verifyDepender($dependerFQSEN, $dependeeFQSEN)) {
                            $response->addRuleViolation(
                                $dependerComponent->getName(),
                                $dependerFQSEN->toString(),
                                $dependeeComponent->getName(),
                                $dependeeFQSEN->toString()
                            );
                        }
                    }

                    if ($component->isBelongedTo($dependerFQSEN->toString())) {
                        if (!$component->verifyDependee($dependeeFQSEN->toString())) {
                            $response->addRuleViolation(
                                $dependerComponent->getName(),
                                $dependerFQSEN->toString(),
                                $dependeeComponent->getName(),
                                $dependeeFQSEN->toString()
                            );
                        }
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

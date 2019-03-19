<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\Exceptions\InvalidRuleDefinition;
use DependencyAnalyzer\Patterns\QualifiedNamePattern;

class DependencyRuleFactory
{
    /**
     * @param array $ruleDefinitions
     * @return DependencyRule[]
     */
    public function create(array $ruleDefinitions): array
    {
        foreach ($ruleDefinitions as $ruleDefinition) {
            $this->verifyDefinition($ruleDefinition);
        }

        $rules = [];
        foreach ($ruleDefinitions as $ruleDefinition) {
            $rules[] = $this->createDependencyRule($ruleDefinition);
        }

        return $rules;
    }

    protected function createDependencyRule(array $ruleDefinition)
    {
        $componentDefines = [];
        foreach ($ruleDefinition as $componentName => $componentDefinition) {
            $componentDefines[$componentName] = $componentDefinition['define'];
        }

        $components = [];
        foreach ($ruleDefinition as $componentName => $componentDefinition) {
            $components[] = $this->createComponent($componentName, $componentDefinition);
        }
        return new DependencyRule($components);
    }

    /**
     * @param string $name
     * @param array $definition
     *   ex: [
     *     'define'   => [...]      // required
     *     'depender' => [...]      // option
     *     'dependee' => [...]      // option
     *   ]
     * @return Component
     */
    protected function createComponent(string $name, array $definition)
    {
        return new Component(
            $name,
            new QualifiedNamePattern($definition['define']),
            isset($definition['depender']) ? new QualifiedNamePattern($definition['depender']) : null,
            isset($definition['dependee']) ? new QualifiedNamePattern($definition['dependee']) : null
        );
    }

    protected function verifyDefinition(array $ruleDefinition): void
    {
        foreach ($ruleDefinition as $componentName => $componentDefinition) {
            if (!isset($componentDefinition['define']) || !is_array($componentDefinition['define'])) {
                throw new InvalidRuleDefinition(
                    $ruleDefinition,
                    "component must have 'define'. Invalid your component: {$componentName}"
                );
            }
        }
    }
}

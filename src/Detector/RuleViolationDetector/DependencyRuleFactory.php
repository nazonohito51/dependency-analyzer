<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\Exceptions\InvalidRuleDefinition;

class DependencyRuleFactory
{
    /**
     * @param array $ruleDefinitions
     * @return DependencyRule[]
     */
    public function create(array $ruleDefinitions): array
    {
        $rules = [];

        foreach ($ruleDefinitions as $ruleDefinition) {
            $this->verifyDefinition($ruleDefinition);

            $rules[] = new DependencyRule($ruleDefinition);
        }

        return $rules;
    }

    protected function verifyDefinition(array $ruleDefinition): bool
    {
        foreach ($ruleDefinition as $componentName => $componentDefinition) {
            if (substr($componentName, 0, 1) !== '@') {
                throw new InvalidRuleDefinition(
                    $ruleDefinition,
                    "component name must start with '@'(ex: @application, @domain). Your component name: {$componentName}"
                );
            } elseif (!isset($componentDefinition['define']) || !is_array($componentDefinition['define'])) {
                throw new InvalidRuleDefinition(
                    $ruleDefinition,
                    "component must have 'define'. Invalid your component: {$componentName}"
                );
            }
        }

        return true;
    }

    /**
     * @param string $path
     * @return DependencyRule[]
     */
    public function createFromPhpFile(string $path): array
    {
        return [];
    }
}

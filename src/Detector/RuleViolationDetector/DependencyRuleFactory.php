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
            $components[] = $this->createComponent($componentName, $componentDefinition, $componentDefines);
        }
        return new DependencyRule($components);
    }

    /**
     * @param string $name
     * @param array $definition
     *   ex: [
     *     'define' => [...]      // required
     *     'white' => [...]       // option
     *     'black' => [...]       // option
     *   ]
     * @param array $componentDefines
     * @return Component
     */
    protected function createComponent(string $name, array $definition, array $componentDefines)
    {
        // TODO: change white/black to depender/dependee
        $dependerPattern = [];
        foreach ($definition['white'] ?? [] as $item) {
            $dependerPattern[] = new QualifiedNamePattern($componentDefines[$item]);
        }
        foreach ($definition['black'] ?? [] as $item) {
            $dependerPattern[] = new QualifiedNamePattern(array_map(function (string $pattern) {
                return '!' . $pattern;
            }, $componentDefines[$item]));
        }

        return new Component($name, new QualifiedNamePattern($definition['define']), $dependerPattern);
    }

    protected function verifyDefinition(array $ruleDefinition): void
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
    }
}

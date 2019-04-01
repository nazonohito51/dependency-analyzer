<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Inspector\RuleViolationDetector;

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
        $rules = [];
        foreach ($ruleDefinitions as $ruleName => $ruleDefinition) {
            $this->verifyDefinition($ruleDefinition);
            if (is_int($ruleName)) {
                $ruleName = (string)$ruleName;
            }
            $rules[] = $this->createDependencyRule($ruleName, $ruleDefinition);
        }

        return $rules;
    }

    protected function createDependencyRule(string $ruleName, array $ruleDefinition)
    {
        $componentDefines = [];
        foreach ($ruleDefinition as $componentName => $componentDefinition) {
            $componentDefines[$componentName] = $componentDefinition['define'];
        }

        $components = [];
        foreach ($ruleDefinition as $componentName => $componentDefinition) {
            $depender = isset($componentDefinition['depender']) ? $this->createDependPattern($componentDefinition['depender'], $componentDefines) : null;
            $dependee = isset($componentDefinition['dependee']) ? $this->createDependPattern($componentDefinition['dependee'], $componentDefines) : null;
            $components[] = new Component(
                $componentName,
                new QualifiedNamePattern($componentDefinition['define']),
                $depender,
                $dependee
            );
        }
        return new DependencyRule($ruleName, $components);
    }

    protected function createDependPattern(array $dependMatchers, array $componentDefines): QualifiedNamePattern
    {
        $matchers = [];
        $excludeMatchers = [];
        foreach ($dependMatchers as $dependMatcher) {
            if (preg_match('/^\![^\\\@]/', $dependMatcher) === 1 && isset($componentDefines[substr($dependMatcher, 1)])) {
                $excludeMatchers = array_merge($excludeMatchers, $componentDefines[substr($dependMatcher, 1)]);
            } elseif (preg_match('/^[^\\\@]/', $dependMatcher) === 1 && isset($componentDefines[$dependMatcher])) {
                $matchers = array_merge($matchers, $componentDefines[$dependMatcher]);
            } else {
                $matchers[] = $dependMatcher;
            }
        }

        // TODO: fix it...
        return (new QualifiedNamePattern($matchers))->addExcludePatterns($excludeMatchers);
    }

    protected function verifyDefinition(array $ruleDefinition): void
    {
        foreach ($ruleDefinition as $componentName => $componentDefinition) {
            if (!isset($componentDefinition['define']) || !is_array($componentDefinition['define'])) {
                throw new InvalidRuleDefinition($ruleDefinition, "component must have 'define'. Invalid your component: {$componentName}");
            } elseif (isset($componentDefinition['depender']) && !is_array($componentDefinition['depender'])) {
                throw new InvalidRuleDefinition($ruleDefinition, "depenee must be array. Invalid your component: {$componentName}");
            } elseif (isset($componentDefinition['dependee']) && !is_array($componentDefinition['dependee'])) {
                throw new InvalidRuleDefinition($ruleDefinition, "depenee must be array. Invalid your component: {$componentName}");
            }
        }
    }
}

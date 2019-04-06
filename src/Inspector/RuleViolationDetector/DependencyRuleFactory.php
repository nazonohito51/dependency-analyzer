<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Inspector\RuleViolationDetector;

use DependencyAnalyzer\Exceptions\InvalidRuleDefinition;
use DependencyAnalyzer\Matcher\ClassNameMatcher;

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
            $depender = isset($componentDefinition['depender']) ? $this->createClassNameMatcher($componentDefinition['depender'], $componentDefines) : null;
            $dependee = isset($componentDefinition['dependee']) ? $this->createClassNameMatcher($componentDefinition['dependee'], $componentDefines) : null;
            $components[] = new Component(
                $componentName,
                new ClassNameMatcher($componentDefinition['define']),
                $depender,
                $dependee
            );
        }
        return new DependencyRule($ruleName, $components);
    }

    protected function createClassNameMatcher(array $dependPatterns, array $componentDefines): ClassNameMatcher
    {
        $patterns = [];
        $excludePatterns = [];
        foreach ($dependPatterns as $dependPattern) {
            if (preg_match('/^\![^\\\@]/', $dependPattern) === 1 && isset($componentDefines[substr($dependPattern, 1)])) {
                // ex: '!component_name' -> ['\Component\Define']
                $excludePatterns = array_merge($excludePatterns, $componentDefines[substr($dependPattern, 1)]);
            } elseif (preg_match('/^[^\\\@]/', $dependPattern) === 1 && isset($componentDefines[$dependPattern])) {
                // ex: 'component_name' -> ['\Component\Define']
                $patterns = array_merge($patterns, $componentDefines[$dependPattern]);
            } else {
                $patterns[] = $dependPattern;
            }
        }

        // TODO: fix it...
        return (new ClassNameMatcher($patterns))->addExcludePatterns($excludePatterns);
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

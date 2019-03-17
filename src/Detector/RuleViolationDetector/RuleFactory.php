<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\Exceptions\InvalidRuleDefinition;

class RuleFactory
{
    public function create(array $ruleDefinitions): array
    {
        $rules = [];

        foreach ($ruleDefinitions as $ruleDefinition) {
            if (!$this->verifyDefinition($ruleDefinition)) {
                throw new InvalidRuleDefinition($ruleDefinition);
            }

            $rules[] = new DependencyRule($ruleDefinition);
        }

        return $rules;
    }

    protected function verifyDefinition(array $ruleDefinition): bool
    {
        foreach ($ruleDefinition as $groupName => $groupDefinition) {
            if (substr($groupName, 0, 1) !== '@') {
                return false;
            } elseif (!isset($groupDefinition['define']) || !is_array($groupDefinition['define'])) {
                return false;
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

<?php
namespace DependencyAnalyzer\Detector;

use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRule;
use DependencyAnalyzer\DependencyGraph;

class RuleViolationDetector
{
    /**
     * @var DependencyRule[]
     */
    protected $rules;

    /**
     * @param DependencyRule[] $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function inspect(DependencyGraph $graph)
    {
        $ruleViolations = [];
        foreach ($this->rules as $rule) {
            $ruleViolations = array_merge($ruleViolations, $rule->isSatisfyBy($graph));
        }

        return $ruleViolations;
    }
}

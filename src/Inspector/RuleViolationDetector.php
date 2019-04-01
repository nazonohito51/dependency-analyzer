<?php
namespace DependencyAnalyzer\Inspector;

use DependencyAnalyzer\Inspector\RuleViolationDetector\DependencyRule;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Inspector\Responses\VerifyDependencyResponse;

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

    /**
     * @param DependencyGraph $graph
     * @return VerifyDependencyResponse[]
     */
    public function inspect(DependencyGraph $graph): array
    {
        $verifyDependencyResponses = [];
        foreach ($this->rules as $rule) {
            $verifyDependencyResponses[] = $rule->isSatisfyBy($graph);
        }

        return $verifyDependencyResponses;
    }
}

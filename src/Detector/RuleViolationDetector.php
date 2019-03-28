<?php
namespace DependencyAnalyzer\Detector;

use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRule;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Responses\VerifyDependencyResponse;

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

<?php
namespace DependencyAnalyzer\Detector;

use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRule;
use DependencyAnalyzer\DirectedGraph;

class RuleViolationDetector
{
    /**
     * @var DependencyRule[]
     */
    protected $rules;

    public function __construct()
    {
//        $this->rules = $rules;
        $this->rules = [
            new DependencyRule([
                'Controller' => [
                    'define' => 'App',
                ],
                'Application' => [
                    'define' => 'Acme\Application',
                    'white' => ['Controller']
                ],
                'Domain' => [
                    'define' => 'Acme\Domain',
                    'black' => ['Controller']
                ]
            ]),
        ];
    }

    public function inspect(DirectedGraph $graph)
    {
        $ruleViolations = [];
        foreach ($this->rules as $rule) {
            $ruleViolations = array_merge($ruleViolations, $rule->isSatisfyBy($graph));
        }

        return $ruleViolations;
    }
}

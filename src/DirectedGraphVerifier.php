<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\Detector\CycleDetector;
use DependencyAnalyzer\Detector\RuleViolationDetector;

class DirectedGraphVerifier
{
    /**
     * @var CycleDetector
     */
    private $cycleDetector;

    /**
     * @var RuleViolationDetector
     */
    private $ruleViolationDetector;

    public function __construct(CycleDetector $cycleDetector, RuleViolationDetector $ruleViolationDetector)
    {
        $this->cycleDetector = $cycleDetector;
        $this->ruleViolationDetector = $ruleViolationDetector;
    }

    /**
     * @param DirectedGraph $graph
     * @return string[]
     */
    public function verify(DirectedGraph $graph): array
    {
        $errors['cycle'] = $this->cycleDetector->inspect($graph);
        $errors['ruleViolation'] = $this->ruleViolationDetector->inspect($graph);

        return $errors;
    }
}

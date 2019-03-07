<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector;

use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRule;
use DependencyAnalyzer\DependencyGraph;
use Tests\TestCase;

class RuleViolationDetectorTest extends TestCase
{
    public function provideInspect()
    {
        $graph = $this->createMock(DependencyGraph::class);

        $ruleNoError = $this->createMock(DependencyRule::class);
        $ruleNoError->method('isSatisfyBy')->with($graph)->willReturn([]);

        $dependErrorMessage = 'hoge do not have to depend on fuga';
        $ruleDependError = $this->createMock(DependencyRule::class);
        $ruleDependError->method('isSatisfyBy')->with($graph)->willReturn([$dependErrorMessage]);

        $beDependedErrorMessage = 'fuga do not have to be depended on fuga';
        $ruleBeDependedError = $this->createMock(DependencyRule::class);
        $ruleBeDependedError->method('isSatisfyBy')->with($graph)->willReturn([$beDependedErrorMessage]);

        return [
            [[$ruleNoError], $graph, []],
            [[$ruleDependError], $graph, [$dependErrorMessage]],
            [[$ruleBeDependedError], $graph, [$beDependedErrorMessage]],
            [[$ruleNoError, $ruleDependError, $ruleBeDependedError], $graph, [$dependErrorMessage, $beDependedErrorMessage]],
        ];
    }

    /**
     * @param array $rules
     * @param DependencyGraph $graph
     * @param array $expected
     * @dataProvider provideInspect
     */
    public function testInspect(array $rules, DependencyGraph $graph, array $expected)
    {
        $detector = new RuleViolationDetector($rules);

        $errors = $detector->inspect($graph);

        $this->assertEquals($expected, $errors);
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class DependencyGraphTest extends TestCase
{
    public function testGroupByPattern()
    {
        $graph = new DependencyGraph($this->getGraph());

        $newGraph = $graph->groupByPattern('MyGroup', new StructuralElementPatternMatcher(['\v3', '\v4']));

        $this->assertSame([
            'MyGroup' => [
                'v5' => [],
                'v6' => []
            ],
            'v1' => [
                'v2' => [],
                'MyGroup' => []
            ],
            'v2' => [
                'MyGroup' => []
            ],
            'v5' => [],
            'v6' => []
        ], $newGraph->toArray());
    }

    public function testToArray()
    {
        $sut = new DependencyGraph($this->getGraph());

        $this->assertEquals([
            'v1' => [
                'v2' => [],
                'v3' => []
            ],
            'v2' => [
                'v3' => [],
                'v4' => []
            ],
            'v3' => [
                'v4' => [],
                'v5' => []
            ],
            'v4' => [
                'v6' => []
            ],
            'v5' => [],
            'v6' => []
        ], $sut->toArray());
    }

    public function testCount()
    {
        $sut = new DependencyGraph($this->getGraph());

        $this->assertCount(6, $sut);
    }

    private function getGraph()
    {
        $graph = new Graph();
        $vertext1 = $graph->createVertex('v1');
        $vertext2 = $graph->createVertex('v2');
        $vertext3 = $graph->createVertex('v3');
        $vertext4 = $graph->createVertex('v4');
        $vertext5 = $graph->createVertex('v5');
        $vertext6 = $graph->createVertex('v6');

        $vertext1->createEdgeTo($vertext2);
        $vertext1->createEdgeTo($vertext3);
        $vertext2->createEdgeTo($vertext3);
        $vertext2->createEdgeTo($vertext4);
        $vertext3->createEdgeTo($vertext4);
        $vertext3->createEdgeTo($vertext5);
        $vertext4->createEdgeTo($vertext6);

        return $graph;
    }
}

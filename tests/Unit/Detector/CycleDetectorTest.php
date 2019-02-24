<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Detector;

use DependencyAnalyzer\Detector\CycleDetector;
use DependencyAnalyzer\DirectedGraph;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class CycleDetectorTest extends TestCase
{
    public function testDetect_shouldNotDetectCycle()
    {
        $graph = new Graph();
        $vertex1 = $graph->createVertex('v1');
        $vertex2 = $graph->createVertex('v2');
        $vertex3 = $graph->createVertex('v3');
        $vertex1->createEdgeTo($vertex2);
        $vertex2->createEdgeTo($vertex3);
        $directedGraph = new DirectedGraph($graph);
        $detector = new CycleDetector();

        $errors = $detector->inspect($directedGraph);

        $this->assertCount(0, $errors);
    }

    public function provideTestDetect_shouldDetectCycle()
    {
        $graph = new Graph();
        $vertex1 = $graph->createVertex('v1');
        $vertex2 = $graph->createVertex('v2');
        $vertex3 = $graph->createVertex('v3');
        $vertex4 = $graph->createVertex('v4');
        $vertex5 = $graph->createVertex('v5');
        $vertex6 = $graph->createVertex('v6');
        $vertex7 = $graph->createVertex('v7');
        $vertex8 = $graph->createVertex('v8');
        $vertex9 = $graph->createVertex('v9');
        $vertex1->createEdgeTo($vertex2);
        $vertex2->createEdgeTo($vertex3);
        $vertex2->createEdgeTo($vertex4);
        $vertex2->createEdgeTo($vertex5);
        $vertex3->createEdgeTo($vertex5);
        $vertex5->createEdgeTo($vertex3);
        $vertex4->createEdgeTo($vertex6);
        $vertex6->createEdgeTo($vertex1);
        $vertex6->createEdgeTo($vertex7);
        $vertex6->createEdgeTo($vertex8);
        $vertex7->createEdgeTo($vertex2);
        $vertex8->createEdgeTo($vertex9);
        $vertex9->createEdgeTo($vertex1);

        return [
            [$this->createSimpleCycleGraph(['v1', 'v2']), [['v1', 'v2']]],
            [$this->createSimpleCycleGraph(['v1', 'v2', 'v3']), [['v1', 'v2', 'v3']]],
            [new DirectedGraph($graph), [
                ['v1', 'v2', 'v4', 'v6'],
                ['v1', 'v2', 'v4', 'v6', 'v8', 'v9'],
                ['v2', 'v4', 'v6', 'v7'],
                ['v3', 'v5']
            ]]
        ];
    }

    /**
     * @param DirectedGraph $graph
     * @param array $expected
     * @dataProvider provideTestDetect_shouldDetectCycle
     */
    public function testDetect_shouldDetectCycle(DirectedGraph $graph, array $expected)
    {
        $detector = new CycleDetector();

        $errors = $detector->inspect($graph);

        $this->assertEquals($expected, $errors);
    }

    /**
     * @param array $ids
     * @return DirectedGraph
     */
    protected function createSimpleCycleGraph(array $ids): DirectedGraph
    {
        $graph = new Graph();
        $vertices = [];

        foreach ($ids as $id) {
            $vertices[] = $graph->createVertex($id);
        }

        for ($i = 0; $i < count($vertices); $i++) {
            $vertices[$i]->createEdgeTo($vertices[($i + 1) % count($vertices)]);
        }

        return new DirectedGraph($graph);
    }
}

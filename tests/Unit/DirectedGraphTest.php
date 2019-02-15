<?php
namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DirectedGraph;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class DirectedGraphTest extends TestCase
{
    public function testCount()
    {
        $graph = new Graph();
        $graph->createVertex('v1');
        $graph->createVertex('v2');
        $graph->createVertex('v3');

        $sut = new DirectedGraph($graph);

        $this->assertCount(3, $sut);
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DirectedGraph\Formatter;

use DependencyAnalyzer\DirectedGraph;
use DependencyAnalyzer\DirectedGraph\Formatter\UmlFormatter;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class UmlFormatterTest extends TestCase
{
    public function testFormat()
    {
        // TODO: remove dependency on graph
        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $v3 = $graph->createVertex('v3');
        $v1->createEdgeTo($v2);
        $v1->createEdgeTo($v3);
        $v2->createEdgeTo($v3);

        $formatter = new UmlFormatter(new DirectedGraph($graph));

        $this->assertEquals(<<<EOT
@startuml
v1 --> v2
v1 --> v3
v2 --> v3
@enduml
EOT
, $formatter->format());
    }
}

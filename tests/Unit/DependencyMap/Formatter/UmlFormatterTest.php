<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph\Formatter;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\Formatter\UmlFormatter;
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

        $formatter = new UmlFormatter(new DependencyGraph($graph));

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

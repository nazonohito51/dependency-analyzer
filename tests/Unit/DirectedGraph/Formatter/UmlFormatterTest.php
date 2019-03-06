<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DirectedGraph\Formatter;

use DependencyAnalyzer\DirectedGraph\Formatter\UmlFormatter;
use Tests\TestCase;

class UmlFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new UmlFormatter($graph);

        $this->assertEquals('@startuml @enduml', $formatter->format());
    }
}

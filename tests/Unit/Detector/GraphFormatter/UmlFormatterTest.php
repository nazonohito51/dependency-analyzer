<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph\Detector\GraphFormatter;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Detector\GraphFormatter\UmlFormatter;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class UmlFormatterTest extends TestCase
{
    public function provideFormat()
    {
        // TODO: remove dependency on graph
        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $v3 = $graph->createVertex('v3');
        $v1->createEdgeTo($v2);
        $v1->createEdgeTo($v3);
        $v2->createEdgeTo($v3);
        $dependencyGraph = new DependencyGraph($graph);

        $expectedWithoutRule = <<<EOT
@startuml
class v1 {
}
class v2 {
}
class v3 {
}
v1 --> v2
v1 --> v3
v2 --> v3
@enduml
EOT;

        $expectedWithRule = <<<EOT
@startuml
namespace Namespace1 {
class v1 {
}
class v2 {
}
}
namespace Namespace2 {
class v3 {
}
}
Namespace1.v1 --> Namespace1.v2
Namespace1.v1 --> Namespace2.v3
Namespace1.v2 --> Namespace2.v3
@enduml
EOT;

        $expectedWithExcludeRule = <<<EOT
@startuml
namespace Namespace1 {
class v1 {
}
}
namespace Namespace2 {
class v3 {
}
}
Namespace1.v1 --> Namespace2.v3
@enduml
EOT;

        $expectedWithGroupRule = <<<EOT
@startuml
namespace Namespace1 {
class MyGroup {
}
}
namespace Namespace2 {
class v3 {
}
}
Namespace1.MyGroup --> Namespace2.v3
@enduml
EOT;

        return [
            'without rule' => [$dependencyGraph, [], $expectedWithoutRule],
            'with rule' => [$dependencyGraph, [
                'namespace' => [
                    'Namespace1' => ['\v1', '\v2'],
                    'Namespace2' => ['\v3'],
                ]
            ], $expectedWithRule],
            'with exclude rule' => [$dependencyGraph, [
                'namespace' => [
                    'Namespace1' => ['\v1'],
                    'Namespace2' => ['\v3'],
                ],
                'exclude' => ['\v2']
            ], $expectedWithExcludeRule],
            'with group rule' => [$dependencyGraph, [
                'namespace' => [
                    'Namespace1' => ['\MyGroup'],
                    'Namespace2' => ['\v3'],
                ],
                'group' => [
                    'MyGroup' => ['\v1', '\v2']
                ]
            ], $expectedWithGroupRule],
        ];
    }

    /**
     * @param DependencyGraph $graph
     * @param array $ruleDefinition
     * @param string $expected
     * @dataProvider provideFormat
     */
    public function testFormat(DependencyGraph $graph, array $ruleDefinition, string $expected)
    {
        $formatter = new UmlFormatter($graph, $ruleDefinition);

        $this->assertEquals($expected, $formatter->format());
    }
}

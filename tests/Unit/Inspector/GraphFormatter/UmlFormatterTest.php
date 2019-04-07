<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraph\Detector\GraphFormatter;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Inspector\GraphFormatter\UmlFormatter;
use DependencyAnalyzer\Inspector\RuleViolationDetector\Component;
use DependencyAnalyzer\Matcher\ClassNameMatcher;
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
//    public function testFormat(DependencyGraph $graph, array $ruleDefinition, string $expected)
//    {
//        $formatter = new UmlFormatter($graph, $ruleDefinition);
//
//        $this->assertEquals($expected, $formatter->format());
//    }

    public function provideFormat_WithoutComponent()
    {
        $expected = <<<EOT
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

        return [
            [$this->createDependencyGraph(), [], $expected]
        ];
    }

    public function provideFormat_WithComponent()
    {
        $component1 = $this->createMock(Component::class);
        $component1->method('getName')->willReturn('MyComponent1');
        $component1->method('isBelongedTo')->willReturnMap([
            ['v1', true],
            ['v2', true],
            ['v3', false],
        ]);

        $component2 = $this->createMock(Component::class);
        $component2->method('getName')->willReturn('MyComponent2');
        $component2->method('isBelongedTo')->willReturnMap([
            ['v1', false],
            ['v2', false],
            ['v3', true],
        ]);

        $expected = <<<EOT
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

        return [
            [$this->createDependencyGraph(), [$component1, $component2], $expected]
        ];
    }


    public function provideFormat_WithComponent_HaveNamespace()
    {
        $component1 = $this->createMock(Component::class);
        $component1->method('getName')->willReturn('MyComponent1');
        $component1->method('isBelongedTo')->willReturnMap([
            ['v1', true],
            ['v2', true],
            ['v3', false],
        ]);
        $component1->method('getAttribute')->willReturnMap([
            ['namespace', true],
            ['folding', false],
        ]);

        $component2 = $this->createMock(Component::class);
        $component2->method('getName')->willReturn('MyComponent2');
        $component2->method('isBelongedTo')->willReturnMap([
            ['v1', false],
            ['v2', false],
            ['v3', true],
        ]);
        $component2->method('getAttribute')->willReturnMap([
            ['namespace', true],
            ['folding', false],
        ]);

        $expected = <<<EOT
@startuml
namespace MyComponent1 {
class v1 {
}
class v2 {
}
}
namespace MyComponent2 {
class v3 {
}
}
MyComponent1.v1 --> MyComponent1.v2
MyComponent1.v1 --> MyComponent2.v3
MyComponent1.v2 --> MyComponent2.v3
@enduml
EOT;

        return [
            [$this->createDependencyGraph(), [$component1, $component2], $expected]
        ];
    }

    public function provideFormat_WithComponent_HaveFolding()
    {
        $matcher = $this->createMock(ClassNameMatcher::class);
        $matcher->method('isMatch')->willReturnMap([
            ['v1', true],
            ['v2', true],
            ['v3', false],
            ['MyComponent1', false],
        ]);
        $component1 = $this->createMock(Component::class);
        $component1->method('getName')->willReturn('MyComponent1');
        $component1->method('getDefineMatcher')->willReturn($matcher);
        $component1->method('isBelongedTo')->willReturnMap([
            ['v1', true],
            ['v2', true],
            ['v3', false],
            ['MyComponent1', false]
        ]);
        $component1->method('getAttribute')->willReturnMap([
            ['namespace', false],
            ['folding', true],
        ]);

        $matcher = $this->createMock(ClassNameMatcher::class);
        $matcher->method('isMatch')->willReturnMap([
            ['v1', false],
            ['v2', false],
            ['v3', true],
            ['MyComponent1', false],
        ]);
        $component2 = $this->createMock(Component::class);
        $component2->method('getName')->willReturn('MyComponent2');
        $component2->method('getDefineMatcher')->willReturn($matcher);
        $component2->method('isBelongedTo')->willReturnMap([
            ['v1', false],
            ['v2', false],
            ['v3', true],
            ['MyComponent1', false]
        ]);
        $component2->method('getAttribute')->willReturnMap([
            ['namespace', false],
            ['folding', false],
        ]);

        $expected = <<<EOT
@startuml
class MyComponent1 {
}
class v3 {
}
MyComponent1 --> v3
@enduml
EOT;

        return [
            [$this->createDependencyGraph(), [$component1, $component2], $expected]
        ];
    }

    /**
     * @dataProvider provideFormat_WithComponent
     * @dataProvider provideFormat_WithoutComponent
     * @dataProvider provideFormat_WithComponent_HaveNamespace
     * @dataProvider provideFormat_WithComponent_HaveFolding
     */
    public function testFormat(DependencyGraph $graph, array $components, string $expected)
    {
        $formatter = new UmlFormatter($graph, $components);

        $this->assertEquals($expected, $formatter->format());
    }

    protected function createDependencyGraph(): DependencyGraph
    {
        // TODO: remove dependency on graph
        $graph = new Graph();
        $v1 = $graph->createVertex('v1');
        $v2 = $graph->createVertex('v2');
        $v3 = $graph->createVertex('v3');
        $v1->createEdgeTo($v2);
        $v1->createEdgeTo($v3);
        $v2->createEdgeTo($v3);
        return new DependencyGraph($graph);
    }
}

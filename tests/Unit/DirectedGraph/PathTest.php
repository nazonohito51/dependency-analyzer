<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DirectedGraph;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class PathTest extends TestCase
{
    public function testAddEdge()
    {
        $edge = $this->createMock(Directed::class);
        $path = new Path();

        $newPath = $path->addEdge($edge);

        $this->assertCount(1, $newPath);
        $this->assertCount(0, $path);
    }

    /**
     * @expectedException \DependencyAnalyzer\Exceptions\InvalidEdgeOnPathException
     */
    public function testAddEdge_WhenCantConnectVertex()
    {
        $endEdge = $this->getMockBuilder(Directed::class)
            ->disableOriginalConstructor()
            ->setMethods(['getVertexEnd', 'getId'])
            ->getMock();
        $endEdge->method('getVertexEnd')->willReturnSelf();
        $endEdge->method('getId')->willReturn('v1');
        $path = new Path([$endEdge]);
        $addEdge = $this->getMockBuilder(Directed::class)
            ->disableOriginalConstructor()
            ->setMethods(['getVertexStart', 'getId'])
            ->getMock();
        $addEdge->method('getVertexStart')->willReturnSelf();
        $addEdge->method('getId')->willReturn('v2');

        $path->addEdge($addEdge);
    }

    public function provideHaveCycle()
    {
        return [
            [['v1', 'v2'], false],
            [['v1', 'v2', 'v1'], true],
            [['v1', 'v2', 'v3'], false],
            [['v1', 'v2', 'v3', 'v1'], true],
            [['v1', 'v2', 'v3', 'v2'], true],
            [['v1', 'v2', 'v3', 'v3'], true],
            [['v1', 'v2', 'v3', 'v2', 'v4'], true],
        ];
    }

    /**
     * @dataProvider provideHaveCycle
     * @param array $vertexIds
     * @param bool $expected
     */
    public function testHaveCycle(array $vertexIds, bool $expected)
    {
        $path = new Path($this->createEdges($vertexIds));

        $this->assertEquals($expected, $path->haveCycle());
    }

    public function provideIsSimpleCycle()
    {
        return [
            [['v1', 'v2'], false],
            [['v1', 'v2', 'v1'], true],
            [['v1', 'v2', 'v3'], false],
            [['v1', 'v2', 'v3', 'v1'], true],
            [['v1', 'v2', 'v3', 'v2'], false],
            [['v1', 'v2', 'v3', 'v3'], false],
            [['v1', 'v2', 'v3', 'v1', 'v4'], false],
            [['v1', 'v2', 'v3', 'v2', 'v4'], false],
            [['v1', 'v2', 'v3', 'v1', 'v2', 'v3', 'v1'], false],
        ];
    }

    /**
     * @param array $vertexIds
     * @param bool $expected
     * @dataProvider provideIsSimpleCycle
     */
    public function testIsSimpleCycle(array $vertexIds, bool $expected)
    {
        $path = new Path($this->createEdges($vertexIds));

        $this->assertEquals($expected, $path->isSimpleCycle());
    }

    public function provideIsEqual()
    {
        return [
            'non edge 1' => [[], [], false],
            'non edge 2' => [['v1'], ['v1'], false],
            'non edge 3' => [['v1'], ['v2'], false],
            'different edge count 1' => [['v1', 'v2', 'v3'], ['v1', 'v2'], false],
            'different edge count 2' => [['v1', 'v2'], ['v1', 'v2', 'v3'], false],
            'cycle and non cycle 1' => [['v1', 'v2', 'v3', 'v1'], ['v1', 'v2', 'v3'], false],
            'cycle and simple cycle 1' => [['v1', 'v2', 'v3', 'v1', 'v2'], ['v1', 'v2', 'v3', 'v1'], false],
            'cycle 1' => [['v1', 'v2', 'v1'], ['v1', 'v2', 'v1'], true],
            'cycle 2' => [['v1', 'v2', 'v1'], ['v2', 'v1', 'v2'], true],
            'cycle 3' => [['v1', 'v2', 'v3', 'v1'], ['v1', 'v2', 'v3', 'v1'], true],
            'cycle 4' => [['v1', 'v2', 'v3', 'v1'], ['v3', 'v1', 'v2', 'v3'], true],
            'non cycle 1' => [['v1', 'v2'], ['v1', 'v2'], true],
            'non cycle 2' => [['v1', 'v2'], ['v2', 'v1'], false],
            'non cycle 3' => [['v1', 'v2', 'v3'], ['v1', 'v2', 'v3'], true],
        ];
    }

    /**
     * @param array $deciderIds
     * @param array $decideeIds
     * @param bool $expected
     * @dataProvider provideIsEqual
     */
    public function testIsEqual(array $deciderIds, array $decideeIds, bool $expected)
    {
        $decider = new Path($this->createEdges($deciderIds));
        $decidee = new Path($this->createEdges($decideeIds));

        $this->assertEquals($expected, $decider->isEqual($decidee));
    }

    /**
     * @param array $vertexIds
     * @return Directed[]
     */
    protected function createEdges(array $vertexIds): array
    {
        // TODO: remove dependency to Graph
        $graph = new Graph();
        foreach ($vertexIds as $vertexId) {
            $vertex = $graph->createVertex($vertexId, true);
            if (isset($before)) {
                $before->createEdgeTo($vertex);
            }
            $before = $vertex;
        }

        return $graph->getEdges()->getVector();
    }
}

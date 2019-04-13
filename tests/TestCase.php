<?php
declare(strict_types=1);

namespace Tests;

use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->getTmpDir(), [$this->getTmpDir() . '.gitkeep']);
    }

    protected function cleanDirectory($directory, array $excludeFiles = [])
    {
        foreach(new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        ) as $file) {
            if (!in_array($file->getPathname(), $excludeFiles)) {
                if ($file->isDir()) {
                    @rmdir($file->getPathname());
                } else {
                    @unlink($file->getPathname());
                }
            }
        }
    }

    /**
     * @return string
     */
    protected function getRootDir(): string
    {
        return realpath(__DIR__ . '/../');
    }

    /**
     * @param $name
     * @return string
     */
    public function getFixturePath($name): string
    {
        return $this->getRootDir() . '/tests/fixtures' . (substr($name, 0, 1) === '/' ? $name : "/{$name}");
    }

    /**
     * @return string
     */
    protected function getTmpDir(): string
    {
        return $this->getRootDir() . '/tests/tmp/';
    }

    protected function assertGraphEquals(Graph $expected, Graph $actual)
    {
        $f = function(Graph $graph){
            $ret = get_class($graph);
            $ret .= PHP_EOL . 'vertices: ' . count($graph->getVertices());
            $ret .= PHP_EOL . 'edges: ' . count($graph->getEdges());

            return $ret;
        };

        $this->assertEquals($f($expected), $f($actual));
        $this->assertCount(count($expected->getVertices()->getMap()), $actual->getVertices()->getMap());

        foreach ($expected->getVertices()->getMap() as $vid => $vertex) {
            $this->assertVertexEquals($vertex, $actual->getVertex($vid));
        }
    }

    protected function assertVertexEquals(Vertex $expected, Vertex $actual)
    {
        $this->assertEquals($expected->getId(), $actual->getId());
        $this->assertCount(count($expected->getAttributeBag()->getAttributes()), $actual->getAttributeBag()->getAttributes());
        foreach ($expected->getAttributeBag()->getAttributes() as $key => $value) {
            $this->assertEquals($value, $actual->getAttribute($key));
        }

        $expectedEdgeOut = $expected->getEdgesOut();
        $this->assertCount(count($expectedEdgeOut), $actual->getEdgesOut());
        foreach ($expectedEdgeOut as $expectedEdge) {
            /** @var Directed $expectedEdge */
            $edge = $this->getEdgeByVertexId($actual, $expectedEdge->getVertexEnd()->getId());
            $this->assertNotNull($edge);
            $this->assertCount(count($expectedEdge->getAttributeBag()->getAttributes()), $edge->getAttributeBag()->getAttributes());

            foreach ($expectedEdge->getAttributeBag()->getAttributes() as $key => $value) {
                $this->assertEquals($value, $edge->getAttribute($key));
            }
        }
    }

    protected function getEdgeByVertexId(Vertex $vertex, string $targetId): ?Directed
    {
        foreach ($vertex->getEdgesOut() as $edge) {
            /** @var Directed $edge */
            if ($edge->getVertexEnd()->getId() === $targetId) {
                return $edge;
            }
        }

        return null;
    }
}

<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use Fhaculty\Graph\Graph;

class DependencyDumper
{
    /**
     * @var \PHPStan\Dependency\DependencyDumper
     */
    private $dumper;

    public function __construct(TestDumper $dumper)
    {
        $this->dumper = $dumper;
    }

    /**
     * @param string[] $files
     * @return DirectedGraph
     */
    public function dump(array $files): DirectedGraph
    {
        $dependencies = $this->dumper->dumpDependencies($files, function() {}, function() {}, null);

        return new DirectedGraph($this->dependenciesToGraph($dependencies));
    }

    private function dependenciesToGraph(array $dependencies)
    {
        $graph = new Graph();
        $vertices = array();

        foreach ($dependencies as $depender => $dependees) {
            if (!isset($vertices[$depender])) {
                $vertices[$depender] = $graph->createVertex($depender);
            }

            foreach ($dependees as $dependee) {
                if (!isset($vertices[$dependee])) {
                    $vertices[$dependee] = $graph->createVertex($dependee);
                }
            }
        }

        foreach ($vertices as $vertex) {
            if (isset($dependencies[$vertex->getId()])) {
                foreach ($dependencies[$vertex->getId()] as $dependency) {
                    $vertex->createEdgeTo($vertices[$dependency]);
                };
            }
        }

        return $graph;
    }
}

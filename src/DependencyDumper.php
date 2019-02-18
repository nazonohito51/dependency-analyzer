<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use Fhaculty\Graph\Graph;
use Symfony\Component\Finder\Finder;

class DependencyDumper
{
    /**
     * @var \PHPStan\Dependency\DependencyDumper
     */
    private $dumper;

    public function __construct(\PHPStan\Dependency\DependencyDumper $dumper)
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

        foreach (array_keys($dependencies) as $file) {
            $vertices[$file] = $graph->createVertex($file);
        }

        foreach ($vertices as $vertex) {
            foreach ($dependencies[$vertex->getId()] as $dependency) {
                $vertex->createEdgeTo($vertices[$dependency]);
            };
        }

        return $graph;
    }
}

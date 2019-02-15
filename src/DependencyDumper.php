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
    /**
     * @var Finder
     */
    private $finder;

    public function __construct(\PHPStan\Dependency\DependencyDumper $dumper, Finder $finder)
    {
        $this->dumper = $dumper;
        $this->finder = $finder;
    }

    public function dump($path): DirectedGraph
    {
        $files = $this->pathToFiles($path);
        $dependencies = $this->dumper->dumpDependencies($files, function() {}, function() {}, null);

        return new DirectedGraph($this->dependenciesToGraph($dependencies));
    }

    private function pathToFiles($path): array
    {
        $files = [];

        foreach ($this->finder->files()->in($path) as $file) {
            $files[] = $file->getRealPath();
        };

        return $files;
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

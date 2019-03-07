<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper\FileDependencyResolver;
use Fhaculty\Graph\Graph;

class DependencyDumper
{
    /**
     * @var FileDependencyResolver
     */
    protected $fileDependencyResolver;

    public function __construct(FileDependencyResolver $fileDependencyResolver)
    {
        $this->fileDependencyResolver = $fileDependencyResolver;
    }

    public function dump(array $files): DependencyGraph
    {
        $dependencies = [];
        foreach ($files as $file) {
            $fileDependencies = $this->fileDependencyResolver->dump($file);

            $dependencies = array_merge($dependencies, $fileDependencies);
        }

        return new DependencyGraph($this->dependenciesToGraph($dependencies));
    }

    protected function dependenciesToGraph(array $dependencies)
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

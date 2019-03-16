<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\Formatter;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\ClassLikeAggregate;
use Fhaculty\Graph\Graph;

class DependencyGraphFactory
{
    public function createFromClassLikeAggregate(ClassLikeAggregate $classLikeAggregate)
    {
        $graph = new Graph();

        foreach ($classLikeAggregate->getClassLikes() as $classLike) {
            if (!$graph->hasVertex($classLike->getName())) {
                $vertex = $graph->createVertex($classLike->getName());
                $vertex->setAttribute('reflection', $classLike->getReflection());
            }
            $depender = $graph->getVertex($classLike->getName());

            foreach ($classLike->getDependees() as $dependee) {
                if (!$graph->hasVertex($dependee->getName())) {
                    $vertex = $graph->createVertex($dependee->getName());
                    $vertex->setAttribute('reflection', $dependee);
                }
                $dependee = $graph->getVertex($dependee->getName());

                $depender->createEdgeTo($dependee);
            }
        }

        return new DependencyGraph($graph);
    }
}

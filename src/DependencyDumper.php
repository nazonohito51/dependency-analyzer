<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper\DependencyResolver;
use Fhaculty\Graph\Graph;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;

class DependencyDumper
{
    /** @var DependencyResolver */
    protected $dependencyResolver;

    /** @var NodeScopeResolver */
    protected $nodeScopeResolver;

    /** @var Parser */
    protected $parser;

    /** @var ScopeFactory */
    protected $scopeFactory;

    public function __construct(
        DependencyResolver $dependencyResolver,
        NodeScopeResolver $nodeScopeResolver,
        Parser $parser,
        ScopeFactory $scopeFactory
    )
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->parser = $parser;
        $this->scopeFactory = $scopeFactory;
    }

    public function dump(array $files): DirectedGraph
    {
        $analysedFiles = $files;
//        if ($analysedPaths !== null) {
//            $analysedFiles = $this->fileFinder->findFiles($analysedPaths)->getFiles();
//        }
        $this->nodeScopeResolver->setAnalysedFiles($analysedFiles);
        $analysedFiles = array_fill_keys($analysedFiles, true);

        $dependencies = [];
//        $countCallback(count($files));
        foreach ($files as $file) {
            try {
                $parserNodes = $this->parser->parseFile($file);
            } catch (\PHPStan\Parser\ParserErrorsException $e) {
                continue;
            }

            $fileDependencies = [];
            try {
                $this->nodeScopeResolver->processNodes(
                    $parserNodes,
                    $this->scopeFactory->create(ScopeContext::create($file)),
                    function (\PhpParser\Node $node, Scope $scope) use ($analysedFiles, &$fileDependencies): void {
                        foreach ($this->resolveDependencies($node, $scope, $analysedFiles) as $depender => $dependees) {
                            foreach ($dependees as $dependee) {
                                $fileDependencies = $this->addToDependencies($depender, $dependee, $fileDependencies);
                            }
                        }
                    }
                );
            } catch (\PHPStan\AnalysedCodeException $e) {
                // pass
            }

            $dependencies = array_merge($dependencies, $fileDependencies);
//            $progressCallback();
        }

        return new DirectedGraph($this->dependenciesToGraph($dependencies));
    }

    /**
     * @param \PhpParser\Node $node
     * @param Scope $scope
     * @return string[]
     * @throws \PHPStan\Broker\FunctionNotFoundException
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
     */
    protected function resolveDependencies(\PhpParser\Node $node, Scope $scope): array
    {
        $dependencies = [];

        foreach ($this->dependencyResolver->resolveDependencies($node, $scope) as $dependencyReflection) {
            if ($dependencyReflection instanceof ClassReflection) {
                if ($scope->isInClass()) {
                    if ($scope->getClassReflection()->getDisplayName() === $dependencyReflection->getDisplayName()) {
                        // call same class method/property
                    } else {
                        $className = $scope->getClassReflection()->getDisplayName();
                        $dependencies = $this->addToDependencies($className, $dependencyReflection->getDisplayName(), $dependencies);
                    }
                } else {
                    // Maybe, class declare statement
                    if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                        $dependencies = $this->addToDependencies($node->namespacedName->toString(), $dependencyReflection->getDisplayName(), $dependencies);
                    } elseif ($node instanceof \PhpParser\Node\Stmt\Interface_) {
                        $dependencies = $this->addToDependencies($node->namespacedName->toString(), $dependencyReflection->getDisplayName(), $dependencies);
                    }
                }
            } elseif ($dependencyReflection instanceof PhpFunctionReflection) {
                // function call
            } else {
                // error of DependencyResolver
            }
        }

        return $dependencies;
    }

    protected function addToDependencies(string $depender, string $dependee, array $dependencies)
    {
        if (!isset($dependencies[$depender])) {
            $dependencies[$depender][] = $dependee;
        } elseif (!in_array($dependee, $dependencies[$depender])) {
            $dependencies[$depender][] = $dependee;
        }

        return $dependencies;
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

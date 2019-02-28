<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper\DependencyResolver;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\File\FileFinder;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\Php\PhpFunctionReflection;

class TestDumper
{
    /** @var DependencyResolver */
    private $dependencyResolver;

    /** @var NodeScopeResolver */
    private $nodeScopeResolver;

    /** @var FileHelper */
    private $fileHelper;

    /** @var Parser */
    private $parser;

    /** @var ScopeFactory */
    private $scopeFactory;

    /** @var FileFinder */
    private $fileFinder;



    public $file;
    public $dependencies = [];



    public function __construct(
        DependencyResolver $dependencyResolver,
        NodeScopeResolver $nodeScopeResolver,
        FileHelper $fileHelper,
        Parser $parser,
        ScopeFactory $scopeFactory,
        FileFinder $fileFinder
    )
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->fileHelper = $fileHelper;
        $this->parser = $parser;
        $this->scopeFactory = $scopeFactory;
        $this->fileFinder = $fileFinder;
    }

    /**
     * @param string[] $files
     * @param callable(int $count): void $countCallback
     * @param callable(): void $progressCallback
     * @param string[]|null $analysedPaths
     * @return string[][]
     */
    public function dumpDependencies(
        array $files,
        callable $countCallback,
        callable $progressCallback,
        ?array $analysedPaths
    ): array
    {
        $analysedFiles = $files;
        if ($analysedPaths !== null) {
            $analysedFiles = $this->fileFinder->findFiles($analysedPaths)->getFiles();
        }
        $this->nodeScopeResolver->setAnalysedFiles($analysedFiles);
        $analysedFiles = array_fill_keys($analysedFiles, true);

        $dependencies = [];
        $countCallback(count($files));
        foreach ($files as $file) {
$this->file = $file;
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
                        $fileDependencies = array_merge(
                            $fileDependencies,
                            $this->resolveDependencies($node, $scope, $analysedFiles)
                        );
                    }
                );
            } catch (\PHPStan\AnalysedCodeException $e) {
                // pass
            }

            foreach (array_unique($fileDependencies) as $fileDependency) {
                $relativeDependencyFile = $fileDependency;
                $dependencies[$relativeDependencyFile][] = $file;
            }

            $progressCallback();
        }

        return $dependencies;
    }

    /**
     * @param \PhpParser\Node $node
     * @param Scope $scope
     * @param array<string, true> $analysedFiles
     * @return string[]
     */
    private function resolveDependencies(
        \PhpParser\Node $node,
        Scope $scope,
        array $analysedFiles
    ): array
    {
        $dependencies = [];

        foreach ($this->dependencyResolver->resolveDependencies($node, $scope) as $dependencyReflection) {
            if (1) {
                if (!method_exists($dependencyReflection, 'getDisplayName')) {
                } elseif ($dependencyReflection instanceof PhpFunctionReflection) {
                } elseif ($scope->isInClass() && $scope->getClassReflection()->getDisplayName() === $dependencyReflection->getDisplayName()) {
                } elseif ($scope->isInClass()) {
                    $className = $scope->getClassReflection()->getDisplayName();
                    if (!in_array($dependencyReflection->getDisplayName(), isset($this->dependencies[$className]) ? $this->dependencies[$className] : [])) {
                        $this->dependencies[$className][] = $dependencyReflection->getDisplayName();
                    }
                } else {
                    if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                        if (!in_array($dependencyReflection->getDisplayName(), isset($this->dependencies[$node->namespacedName->toString()]) ? $this->dependencies[$node->namespacedName->toString()] : [])) {
                            $this->dependencies[$node->namespacedName->toString()][] = $dependencyReflection->getDisplayName();
                        }
                    } elseif ($node instanceof \PhpParser\Node\Stmt\Interface_) {
                        if (!in_array($dependencyReflection->getDisplayName(), isset($this->dependencies[$node->namespacedName->toString()]) ? $this->dependencies[$node->namespacedName->toString()] : [])) {
                            $this->dependencies[$node->namespacedName->toString()][] = $dependencyReflection->getDisplayName();
                        }
                    }
                }
            }

            $dependencyFile = $dependencyReflection->getFileName();
            if ($dependencyFile === false) {
                continue;
            }
            $dependencyFile = $this->fileHelper->normalizePath($dependencyFile);

            if ($scope->getFile() === $dependencyFile) {
                continue;
            }

            if (!isset($analysedFiles[$dependencyFile])) {
                continue;
            }

            $dependencies[$dependencyFile] = $dependencyFile;
        }

        return array_values($dependencies);
    }
}

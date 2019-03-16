<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper\CollectDependenciesVisitor;
use DependencyAnalyzer\DependencyGraph\ClassLike;
use DependencyAnalyzer\DependencyGraph\ClassLikeAggregate;
use DependencyAnalyzer\DependencyGraph\Formatter\DependencyGraphFactory;
use DependencyAnalyzer\Exceptions\UnexpectedException;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Parser\Parser;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\File\FileFinder;

class DependencyDumper
{
    /**
     * @var CollectDependenciesVisitor
     */
    protected $collectNodeVisitor;

    /**
     * @var NodeScopeResolver
     */
    protected $nodeScopeResolver;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var ScopeFactory
     */
    protected $scopeFactory;

    /**
     * @var FileFinder
     */
    protected $fileFinder;

    /**
     * @var DependencyGraphFactory
     */
    protected $dependencyGraphFactory;

    public function __construct(
        NodeScopeResolver $nodeScopeResolver,
        Parser $parser,
        ScopeFactory $scopeFactory,
        FileFinder $fileFinder,
        CollectDependenciesVisitor $collectNodeVisitor,
        DependencyGraphFactory $dependencyGraphFactory
    )
    {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->parser = $parser;
        $this->scopeFactory = $scopeFactory;
        $this->fileFinder = $fileFinder;
        $this->collectNodeVisitor = $collectNodeVisitor;
        $this->dependencyGraphFactory = $dependencyGraphFactory;
    }

    public static function createFromConfig(string $currentDir, string $tmpDir, array $additionalConfigFiles): self
    {
        $phpStanContainer = (new ContainerFactory($currentDir))->create($tmpDir, $additionalConfigFiles, []);

        return new self(
            $phpStanContainer->getByType(NodeScopeResolver::class),
            $phpStanContainer->getByType(Parser::class),
            $phpStanContainer->getByType(ScopeFactory::class),
            $phpStanContainer->getByType(FileFinder::class),
            $phpStanContainer->getByType(CollectDependenciesVisitor::class),
            new DependencyGraphFactory()
        );
    }

    public function dump(array $paths, array $excludePaths = []): DependencyGraph
    {
        $excludeFiles = $this->getAllFilesRecursive($excludePaths);

        $dependencies = new ClassLikeAggregate();
        foreach ($this->getAllFilesRecursive($paths) as $file) {
            if (!in_array($file, $excludeFiles)) {
                $fileDependencies = $this->dumpFile($file);

                $dependencies->merge(new ClassLikeAggregate($fileDependencies));
            }
        }

        return $this->dependencyGraphFactory->createFromClassLikeAggregate($dependencies);
    }

    /**
     * @param string $file
     * @return ClassLike[]
     */
    protected function dumpFile(string $file): array
    {
        try {
            $this->nodeScopeResolver->processNodes(
                $this->parser->parseFile($file),
                $this->scopeFactory->create(ScopeContext::create($file)),
                \Closure::fromCallable($this->collectNodeVisitor)  // type hint of processNodes() is \Closure...
            );
        } catch (AnalysedCodeException $e) {
            throw new UnexpectedException('parsing file is failed: ' . $file);
        }

        return $this->collectNodeVisitor->getDependencies();
    }

    protected function getAllFilesRecursive(array $paths): array
    {
        try {
            $fileFinderResult = $this->fileFinder->findFiles($paths);
        } catch (\PHPStan\File\PathNotFoundException $e) {
            throw new UnexpectedException('path was not found: ' . $e->getPath());
        }

        return $fileFinderResult->getFiles();
    }
}

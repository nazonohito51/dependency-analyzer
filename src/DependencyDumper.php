<?php
declare(strict_types=1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper\DependencyResolveVisitor;
use DependencyAnalyzer\DependencyDumper\NodeDependencyResolver;
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
     * @var NodeDependencyResolver
     */
    protected $dependencyResolverVisitor;

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

    public function __construct(
        NodeScopeResolver $nodeScopeResolver,
        Parser $parser,
        ScopeFactory $scopeFactory,
        FileFinder $fileFinder,
        DependencyResolveVisitor $dependencyResolverVisitor
    )
    {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->parser = $parser;
        $this->scopeFactory = $scopeFactory;
        $this->fileFinder = $fileFinder;
        $this->dependencyResolverVisitor = $dependencyResolverVisitor;
    }

    public static function createFromConfig(string $currentDir, string $tmpDir, array $additionalConfigFiles): self
    {
        $phpStanContainer = (new ContainerFactory($currentDir))->create($tmpDir, $additionalConfigFiles, []);

        return new self(
            $phpStanContainer->getByType(NodeScopeResolver::class),
            $phpStanContainer->getByType(Parser::class),
            $phpStanContainer->getByType(ScopeFactory::class),
            $phpStanContainer->getByType(FileFinder::class),
            $phpStanContainer->getByType(DependencyResolveVisitor::class)
        );
    }

    public function dump(array $paths): DependencyGraph
    {
        $dependencies = [];
        foreach ($this->getAllFilesRecursive($paths) as $file) {
            $fileDependencies = $this->dumpFile($file);

            $dependencies = array_merge($dependencies, $fileDependencies);
        }

        return DependencyGraph::createFromArray($dependencies);
    }

    protected function dumpFile(string $file): array
    {
        try {
            $this->nodeScopeResolver->processNodes(
                $this->parser->parseFile($file),
                $this->scopeFactory->create(ScopeContext::create($file)),
                \Closure::fromCallable($this->dependencyResolverVisitor)  // type hint of processNodes is \Closure...
            );
        } catch (AnalysedCodeException $e) {
            throw new UnexpectedException('parsing file is failed: ' . $file);
        }

        return $this->dependencyResolverVisitor->getDependencies();
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

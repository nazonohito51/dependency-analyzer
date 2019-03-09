<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyDumper\FileDependencyResolver\DependencyResolveVisitor;
use DependencyAnalyzer\DependencyDumper\FileDependencyResolver\NodeDependencyResolver;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Parser\Parser;

class FileDependencyResolver
{
    /** @var NodeDependencyResolver */
    protected $dependencyResolverVisitor;

    /** @var NodeScopeResolver */
    protected $nodeScopeResolver;

    /** @var Parser */
    protected $parser;

    /** @var ScopeFactory */
    protected $scopeFactory;

    public function __construct(
        DependencyResolveVisitor $dependencyResolverVisitor,
        NodeScopeResolver $nodeScopeResolver,
        Parser $parser,
        ScopeFactory $scopeFactory
    )
    {
        $this->dependencyResolverVisitor = $dependencyResolverVisitor;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->parser = $parser;
        $this->scopeFactory = $scopeFactory;
    }

    public function dump(string $file): array
    {
        try {
            $this->nodeScopeResolver->processNodes(
                $this->parser->parseFile($file),
                $this->scopeFactory->create(ScopeContext::create($file)),
                \Closure::fromCallable($this->dependencyResolverVisitor)
            );
        } catch (\PHPStan\AnalysedCodeException $e) {
            // TODO: If there is file that can not is loaded.
        }

        return $this->dependencyResolverVisitor->getDependencies();
    }
}

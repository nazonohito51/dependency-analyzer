<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyDumper\CollectDependenciesVisitor;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\Formatter\DependencyGraphFactory;
use PhpParser\Node;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\File\FileFinder;
use PHPStan\File\FileFinderResult;
use PHPStan\Parser\Parser;
use Tests\TestCase;

class DependencyDumperTest extends TestCase
{
    public function testCreateFromConfig()
    {
        $dependencyDumper = DependencyDumper::createFromConfig(
            $this->getRootDir(),
            $this->getTmpDir(),
            [$this->getRootDir() . '/conf/config.neon']
        );

        $this->assertInstanceOf(DependencyDumper::class, $dependencyDumper);
    }

    public function testDump()
    {
        $analyzePath = $this->getFixturePath('single_directed');
        $analyzeFiles = ["{$analyzePath}/A.php", "{$analyzePath}/B.php", "{$analyzePath}/C.php"];
        $fileFinder = $this->createFileFinder([$analyzePath], $analyzeFiles, [], []);
        $parser = $this->createParser(3);
        $scopeFactory = $this->createScopeFactory(3);
        $collectDependenciesVisitor = $this->createMock(CollectDependenciesVisitor::class);
        $collectDependenciesVisitor->method('__invoke');
        $nodeScopeResolver = $this->createNodeScopeResolver(3);
        $dependencyDumper = new DependencyDumper($nodeScopeResolver, $parser, $scopeFactory, $fileFinder, $collectDependenciesVisitor);

        $dependencyDumper->dump([$analyzePath]);
    }

    public function testDumpSpecifyExcludePath()
    {
        $analyzePath = $this->getFixturePath('single_directed');
        $analyzeFiles = ["{$analyzePath}/A.php", "{$analyzePath}/B.php", "{$analyzePath}/C.php"];
        $excludePath = $this->getFixturePath('single_directed/C.php');
        $excludeFiles = ["{$analyzePath}/C.php"];
        $fileFinder = $this->createFileFinder([$analyzePath], $analyzeFiles, [$excludePath], $excludeFiles);

        $parser = $this->createParser(2);
        $scopeFactory = $this->createScopeFactory(2);
        $collectDependenciesVisitor = $this->createMock(CollectDependenciesVisitor::class);
        $collectDependenciesVisitor->method('__invoke');
        $nodeScopeResolver = $this->createNodeScopeResolver(2);
        $dependencyDumper = new DependencyDumper($nodeScopeResolver, $parser, $scopeFactory, $fileFinder, $collectDependenciesVisitor);

        $dependencyDumper->dump([$analyzePath], [$excludePath]);
    }

    /**
     * @param array $analyzePaths
     * @param array $analyzeFiles
     * @param array $excludePaths
     * @param array $excludeFiles
     * @return FileFinder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createFileFinder(array $analyzePaths, array $analyzeFiles, array $excludePaths, array $excludeFiles)
    {
        $analyzeFileFinderResult = $this->createMock(FileFinderResult::class);
        $analyzeFileFinderResult->method('getFiles')->willReturn($analyzeFiles);
        $excludeFileFinderResult = $this->createMock(FileFinderResult::class);
        $excludeFileFinderResult->method('getFiles')->willReturn($excludeFiles);

        $fileFinder = $this->createMock(FileFinder::class);
        $fileFinder->method('findFiles')->willReturnMap([
            [$analyzePaths, $analyzeFileFinderResult],
            [$excludePaths, $excludeFileFinderResult]
        ]);

        return $fileFinder;
    }

    protected function createNodeScopeResolver(int $callCount)
    {
        $nodeScopeResolver = $this->createMock(NodeScopeResolver::class);
        $nodeScopeResolver->expects($this->exactly($callCount))->method('processNodes')->willReturnCallback(function ($nodes, $scope, callable $visitor) {
            $node = $this->createMock(Node::class);
            $scope = $this->createMock(Scope::class);
            $visitor($node, $scope);   // Call CollectDependencyVisitor->__invoke()
        });

        return $nodeScopeResolver;
    }

    /**
     * @param int $callCount
     * @return Parser|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createParser(int $callCount)
    {
        $parser = $this->createMock(Parser::class);
        $parser->expects($this->exactly($callCount))->method('parseFile')->willReturn([]);

        return $parser;
    }

    /**
     * @param int $callCount
     * @return ScopeFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createScopeFactory(int $callCount)
    {
        $scopeFactory = $this->createMock(ScopeFactory::class);
        $scopeFactory->expects($this->exactly($callCount))->method('create');

        return $scopeFactory;
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyDumper\CollectDependenciesVisitor;
use DependencyAnalyzer\DependencyGraph;
use PHPStan\Analyser\NodeScopeResolver;
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
        $expectedDependencies = [
            'A' => [],
            'B' => ['A'],
            'C' => ['B'],
        ];
        $analyzePath = $this->getFixturePath('single_directed');
        $analyzeFiles = ["{$analyzePath}/A.php", "{$analyzePath}/B.php", "{$analyzePath}/C.php"];
        $fileFinder = $this->createFileFinder([$analyzePath], $analyzeFiles, [], []);
        $nodeScopeResolver = $this->createNodeScopeResolver();
        $parser = $this->createParser();
        $scopeFactory = $this->createScopeFactory();
        $collectDependenciesVisitor = $this->createMock(CollectDependenciesVisitor::class);
        $collectDependenciesVisitor->method('getDependencies')->will($this->onConsecutiveCalls(
            ['A' => []],
            ['B' => ['A']],
            ['C' => ['B']]
        ));
        $dependencyDumper = new DependencyDumper($nodeScopeResolver, $parser, $scopeFactory, $fileFinder, $collectDependenciesVisitor);

        $dependencyGraph = $dependencyDumper->dump([$analyzePath]);

        $this->assertInstanceOf(DependencyGraph::class, $dependencyGraph);
        $this->assertEquals($expectedDependencies, $dependencyGraph->toArray());
    }

    public function testDumpSpecifyExcludePath()
    {
        $expectedDependencies = [
            'A' => [],
            'B' => ['A']
        ];
        $analyzePath = $this->getFixturePath('single_directed');
        $analyzeFiles = ["{$analyzePath}/A.php", "{$analyzePath}/B.php", "{$analyzePath}/C.php"];
        $excludePath = $this->getFixturePath('single_directed/C.php');
        $excludeFiles = ["{$analyzePath}/C.php"];
        $fileFinder = $this->createFileFinder([$analyzePath], $analyzeFiles, [$excludePath], $excludeFiles);
        $nodeScopeResolver = $this->createNodeScopeResolver();
        $parser = $this->createParser();
        $scopeFactory = $this->createScopeFactory();
        $collectDependenciesVisitor = $this->createMock(CollectDependenciesVisitor::class);
        $collectDependenciesVisitor->method('getDependencies')->will($this->onConsecutiveCalls(
            ['A' => []],
            ['B' => ['A']]
        ));
        $dependencyDumper = new DependencyDumper($nodeScopeResolver, $parser, $scopeFactory, $fileFinder, $collectDependenciesVisitor);

        $dependencyGraph = $dependencyDumper->dump([$analyzePath], [$excludePath]);

        $this->assertInstanceOf(DependencyGraph::class, $dependencyGraph);
        $this->assertEquals($expectedDependencies, $dependencyGraph->toArray());
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

    /**
     * @return NodeScopeResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createNodeScopeResolver()
    {
        $nodeScopeResolver = $this->createMock(NodeScopeResolver::class);
        $nodeScopeResolver->method('processNodes');

        return $nodeScopeResolver;
    }

    /**
     * @return Parser|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createParser()
    {
        $parser = $this->createMock(Parser::class);
        $parser->method('parseFile');

        return $parser;
    }

    /**
     * @return ScopeFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createScopeFactory()
    {
        $scopeFactory = $this->createMock(ScopeFactory::class);
        $scopeFactory->method('create');

        return $scopeFactory;
    }
}

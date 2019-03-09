<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyDumper\FileDependencyResolver\DependencyResolveVisitor;
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
        $fileFinder = $this->createFileFinder($analyzePath);
        $nodeScopeResolver = $this->createNodeScopeResolver();
        $parser = $this->createParser();
        $scopeFactory = $this->createScopeFactory();
        $dependencyResolveVisitor = $this->createDependencyResolveVisitor($expectedDependencies);
        $dependencyDumper = new DependencyDumper($nodeScopeResolver, $parser, $scopeFactory, $fileFinder, $dependencyResolveVisitor);

        $dependencyGraph = $dependencyDumper->dump([$analyzePath]);

        $this->assertInstanceOf(DependencyGraph::class, $dependencyGraph);
        $this->assertEquals($expectedDependencies, $dependencyGraph->toArray());
    }

    /**
     * @param string $analyzePath
     * @return FileFinder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createFileFinder(string $analyzePath)
    {
        $fileFinderResult = $this->createMock(FileFinderResult::class);
        $fileFinderResult->method('getFiles')->willReturn([
            "{$analyzePath}/A.php",
            "{$analyzePath}/B.php",
            "{$analyzePath}/C.php"]
        );

        $fileFinder = $this->createMock(FileFinder::class);
        $fileFinder->method('findFiles')->with([$analyzePath])->willReturn($fileFinderResult);

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

    /**
     * @param array $dependencies
     * @return DependencyResolveVisitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createDependencyResolveVisitor(array $dependencies)
    {
        $dependencyResolveVisitor = $this->createMock(DependencyResolveVisitor::class);
        $dependencyResolveVisitor->method('getDependencies')->willReturn($dependencies);

        return $dependencyResolveVisitor;
    }
}

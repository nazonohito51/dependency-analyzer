<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyGraph;
use PHPStan\File\FileFinder;
use PHPStan\File\FileFinderResult;
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
        $fileFinderResult = $this->createMock(FileFinderResult::class);
        $fileFinderResult->method('getFiles')->willReturn([
            "{$analyzePath}/A.php",
            "{$analyzePath}/B.php",
            "{$analyzePath}/C.php"]
        );
        $fileFinder = $this->createMock(FileFinder::class);
        $fileFinder->method('findFiles')->with([$analyzePath])->willReturn($fileFinderResult);
        $fileDependencyResolver = $this->createMock(DependencyDumper\FileDependencyResolver::class);
        $fileDependencyResolver->method('dump')->willReturnMap([
            ["{$analyzePath}/A.php", ['A' => []]],
            ["{$analyzePath}/B.php", ['B' => ['A']]],
            ["{$analyzePath}/C.php", ['C' => ['B']]],
        ]);
        $dependencyDumper = new DependencyDumper($fileDependencyResolver, $fileFinder);

        $dependencyGraph = $dependencyDumper->dump([$analyzePath]);

        $this->assertInstanceOf(DependencyGraph::class, $dependencyGraph);
        $this->assertEquals([
            'A' => [],
            'B' => ['A'],
            'C' => ['B'],
        ], $dependencyGraph->toArray());
    }
}

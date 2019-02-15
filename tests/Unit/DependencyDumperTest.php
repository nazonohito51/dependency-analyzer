<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class DependencyDumperTest extends TestCase
{
    public function testDump()
    {
        $path = $this->getFixturePath('single_directed');
        $dumper = $this->createMock(\PHPStan\Dependency\DependencyDumper::class);
        $dumper->expects($this->once())->method('dumpDependencies')->willReturn([
            'path/to/A.php' => [],
            'path/to/B.php' => ['path/to/A.php'],
            'path/to/C.php' => ['path/to/B.php'],
        ]);
        $finder = $this->createMock(Finder::class);
        $finder->expects($this->once())->method('files')->willReturnSelf();
        $finder->expects($this->once())->method('in')->willReturn([
            new \SplFileInfo($this->getFixturePath('single_directed/A.php')),
            new \SplFileInfo($this->getFixturePath('single_directed/B.php')),
            new \SplFileInfo($this->getFixturePath('single_directed/C.php')),
        ]);
        $sut = new DependencyDumper($dumper, $finder);

        $actual = $sut->dump($path);

        $this->assertInstanceOf(\DependencyAnalyzer\DirectedGraph::class, $actual);
        $this->assertCount(3, $actual);
    }
}

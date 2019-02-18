<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer;

use DependencyAnalyzer\DependencyDumper;
use Tests\TestCase;

class DependencyDumperTest extends TestCase
{
    public function testDump()
    {
        $files = ['path/to/A.php', 'path/to/A.php', 'path/to/A.php'];
        $dumper = $this->createMock(\PHPStan\Dependency\DependencyDumper::class);
        $dumper->expects($this->once())->method('dumpDependencies')->with($files, function() {}, function() {}, null)->willReturn([
            'path/to/A.php' => [],
            'path/to/B.php' => ['path/to/A.php'],
            'path/to/C.php' => ['path/to/B.php'],
        ]);
        $sut = new DependencyDumper($dumper);

        $actual = $sut->dump($files);

        $this->assertInstanceOf(\DependencyAnalyzer\DirectedGraph::class, $actual);
        $this->assertCount(3, $actual);
    }
}

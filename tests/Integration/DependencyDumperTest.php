<?php
declare(strict_types=1);

namespace Tests\Integration;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DirectedGraph;
use PHPStan\DependencyInjection\ContainerFactory;
use Tests\TestCase;

class DependencyDumperTest extends TestCase
{
    public function setUp()
    {
        spl_autoload_register([$this, 'autoload']);
    }

    public function tearDown()
    {
        spl_autoload_unregister([$this, 'autoload']);
    }

    /**
     * Autoload rule of fixtures classes For Container.
     *
     * @param string $className
     */
    public function autoload(string $className)
    {
        $namespaces = explode('\\', $className);
        if (array_slice($namespaces, 0, 2) === ['Tests', 'Fixtures']) {
            $fileName = implode('/', array_slice(explode('\\', $className), 2)) . '.php';
            require_once $this->getFixturePath("individual_theme/{$fileName}");
        }
    }

    public function provideDump()
    {
        return [
            'no_dependencies' => [
                $this->getFixturePath('/individual_theme/NoDependency.php'),
                []
            ],
            'extend' => [
                $this->getFixturePath('/individual_theme/Extend.php'),
                [
                    'Tests\Fixtures\Extend' => ['Tests\Fixtures\NoDependency'],
                    'Tests\Fixtures\NoDependency' => []
                ]
            ],
            'implement' => [
                $this->getFixturePath('/individual_theme/Implement.php'),
                [
                    'Tests\Fixtures\Implement' => ['Tests\Fixtures\Foundations\SomeInterface'],
                    'Tests\Fixtures\Foundations\SomeInterface' => []
                ]
            ],
            'use_trait' => [
                $this->getFixturePath('/individual_theme/UseTrait.php'),
                [
                    'Tests\Fixtures\UseTrait' => ['Tests\Fixtures\Foundations\SomeTrait'],
                    'Tests\Fixtures\Foundations\SomeTrait' => []
                ]
            ],
            'argument_type' => [
                $this->getFixturePath('/individual_theme/ArgumentType.php'),
                [
                    'Tests\Fixtures\ArgumentType' => ['Tests\Fixtures\Foundations\SomeClass'],
                    'Tests\Fixtures\Foundations\SomeClass' => []
                ]
            ],
            'return_type' => [
                $this->getFixturePath('/individual_theme/ReturnType.php'),
                [
                    'Tests\Fixtures\ReturnType' => ['Tests\Fixtures\Foundations\SomeClass'],
                    'Tests\Fixtures\Foundations\SomeClass' => []
                ]
            ],
            'phpdoc' => [
                $this->getFixturePath('/individual_theme/PhpDoc.php'),
                [
                    'Tests\Fixtures\PhpDoc' => ['Tests\Fixtures\Foundations\SomeClass'],
                    'Tests\Fixtures\Foundations\SomeClass' => []
                ]
            ]
        ];
    }

    /**
     * @param string $fixtureFile
     * @param array $expected
     * @dataProvider provideDump
     */
    public function testDump(string $fixtureFile, array $expected)
    {
        $dependencyDumper = $this->createDependencyDumper();

        $graph = $dependencyDumper->dump([$fixtureFile]);

        $this->assertInstanceOf(DirectedGraph::class, $graph);
        $this->assertCount(count($expected), $graph);
        $this->assertEquals($expected, $graph->toArray());
    }

    /**
     * @return DependencyDumper
     */
    protected function createDependencyDumper()
    {
        $containerFactory = new ContainerFactory($this->getRootDir());
        $container = $containerFactory->create($this->getTmpDir(), [$this->getRootDir() . '/conf/config.neon'], []);

        $dependencyDumper = $container->getByType(DependencyDumper::class);
        return $dependencyDumper;
    }
}

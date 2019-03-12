<?php
declare(strict_types=1);

namespace Tests\Integration;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyGraph;
use PHPStan\DependencyInjection\ContainerFactory;
use Tests\TestCase;

class DependencyDumperTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once $this->getFixturePath('/individual_theme/Foundations/some_functions.php');
        spl_autoload_register([$this, 'autoload']);
    }

    public function tearDown()
    {
        parent::tearDown();
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
                    'Tests\Fixtures\ArgumentType' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'argument_type_by_phpdoc' => [
                $this->getFixturePath('/individual_theme/ArgumentTypeByPhpDoc.php'),
                [
                    'Tests\Fixtures\ArgumentTypeByPhpDoc' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'return_type' => [
                $this->getFixturePath('/individual_theme/ReturnType.php'),
                [
                    'Tests\Fixtures\ReturnType' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'return_type_by_phpdoc' => [
                $this->getFixturePath('/individual_theme/ReturnTypeByPhpDoc.php'),
                [
                    'Tests\Fixtures\ReturnTypeByPhpDoc' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'property' => [
                $this->getFixturePath('/individual_theme/Property.php'),
                [
                    'Tests\Fixtures\Property' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'closure' => [
                $this->getFixturePath('/individual_theme/Closure.php'),
                [
                    'Tests\Fixtures\Closure' => ['Tests\Fixtures\Foundations\SomeClass1', 'Tests\Fixtures\Foundations\SomeClass2', 'Tests\Fixtures\Foundations\SomeClass3'],
                    'Tests\Fixtures\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\Foundations\SomeClass3' => []
                ]
            ],
            'function_call' => [
                $this->getFixturePath('/individual_theme/FunctionCall.php'),
                [
                    'Tests\Fixtures\FunctionCall' => ['Tests\Fixtures\Foundations\SomeClass2'],
                    'Tests\Fixtures\Foundations\SomeClass2' => []
                ]
            ],
            'method_call' => [
                $this->getFixturePath('/individual_theme/MethodCall.php'),
                [
                    'Tests\Fixtures\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\MethodCall' => ['Tests\Fixtures\Foundations\SomeClass1', 'Tests\Fixtures\Foundations\SomeClass2']
                ]
            ],
            'property_fetch' => [
                $this->getFixturePath('/individual_theme/PropertyFetch.php'),
                [
                    'Tests\Fixtures\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\PropertyFetch' => ['Tests\Fixtures\Foundations\SomeClass2', 'Tests\Fixtures\Foundations\SomeClass3'],
                    'Tests\Fixtures\Foundations\SomeClass3' => []
                ]
            ],
            'static_call' => [
                $this->getFixturePath('/individual_theme/StaticCall.php'),
                [
                    'Tests\Fixtures\StaticCall' => ['Tests\Fixtures\Foundations\SomeClass3', 'Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass3' => [],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'class_const_call' => [
                $this->getFixturePath('/individual_theme/ClassConstCall.php'),
                [
                    'Tests\Fixtures\ClassConstCall' => ['Tests\Fixtures\Foundations\SomeClass3'],
                    'Tests\Fixtures\Foundations\SomeClass3' => []
                ]
            ],
            'static_property_fetch' => [
                $this->getFixturePath('/individual_theme/StaticPropertyFetch.php'),
                [
                    'Tests\Fixtures\StaticPropertyFetch' => ['Tests\Fixtures\Foundations\SomeClass3', 'Tests\Fixtures\Foundations\SomeClass2'],
                    'Tests\Fixtures\Foundations\SomeClass3' => [],
                    'Tests\Fixtures\Foundations\SomeClass2' => []
                ]
            ],
            'new_class' => [
                $this->getFixturePath('/individual_theme/NewClass.php'),
                [
                    'Tests\Fixtures\NewClass' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'instance_of' => [
                $this->getFixturePath('/individual_theme/InstanceOfClass.php'),
                [
                    'Tests\Fixtures\InstanceOfClass' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'catch_exception' => [
                $this->getFixturePath('/individual_theme/CatchException.php'),
                [
                    'Tests\Fixtures\CatchException' => ['Tests\Fixtures\Foundations\SomeException'],
                    'Tests\Fixtures\Foundations\SomeException' => []
                ]
            ],
            'object_array' => [
                $this->getFixturePath('/individual_theme/ObjectArray.php'),
                [
                    'Tests\Fixtures\ObjectArray' => ['Tests\Fixtures\Foundations\SomeClass1', 'Tests\Fixtures\Foundations\SomeClass2'],
                    'Tests\Fixtures\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\Foundations\SomeClass2' => []
                ]
            ],
            'array_dim_fetch' => [
                $this->getFixturePath('/individual_theme/ArrayDimFetch.php'),
                [
                    'Tests\Fixtures\ArrayDimFetch' => ['Tests\Fixtures\Foundations\SomeClass3'],
                    'Tests\Fixtures\Foundations\SomeClass3' => []
                ]
            ],
            'foreach_array' => [
                $this->getFixturePath('/individual_theme/ForeachArray.php'),
                [
                    'Tests\Fixtures\ForeachArray' => ['Tests\Fixtures\Foundations\SomeClass2', 'Tests\Fixtures\Foundations\SomeClass3'],
                    'Tests\Fixtures\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\Foundations\SomeClass3' => []
                ]
            ],
            'interface_extend' => [
                $this->getFixturePath('/individual_theme/InterfaceExtend.php'),
                [
                    'Tests\Fixtures\InterfaceExtend' => ['Tests\Fixtures\Foundations\SomeInterface'],
                    'Tests\Fixtures\Foundations\SomeInterface' => []
                ]
            ],
            'abstract_extend' => [
                $this->getFixturePath('/individual_theme/AbstractExtend.php'),
                [
                    'Tests\Fixtures\AbstractExtend' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => []
                ]
            ],
            'abstract_implement' => [
                $this->getFixturePath('/individual_theme/AbstractImplement.php'),
                [
                    'Tests\Fixtures\Foundations\SomeInterface' => [],
                    'Tests\Fixtures\AbstractImplement' => ['Tests\Fixtures\Foundations\SomeInterface']
                ]
            ],
            'depend_on_comment' => [
                $this->getFixturePath('/individual_theme/DependOnComment.php'),
                [
                    'Tests\Fixtures\DependOnComment' => ['Tests\Fixtures\Foundations\SomeClass1'],
                    'Tests\Fixtures\Foundations\SomeClass1' => [],
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

        $this->assertInstanceOf(DependencyGraph::class, $graph);
        $this->assertEquals($expected, $graph->toArray());
    }

    /**
     * @return DependencyDumper
     */
    protected function createDependencyDumper()
    {
        return DependencyDumper::createFromConfig($this->getRootDir(), $this->getTmpDir(), [$this->getRootDir() . '/conf/config.neon'], []);
    }
}

<?php
declare(strict_types=1);

namespace Tests\Integration;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyGraph;
use Tests\TestCase;

class DependencyDumperTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once $this->getFixturePath('/individual_theme/Foundations/some_functions.php');
        require_once $this->getFixturePath('/all_theme/foundations.php');
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
        if (array_slice($namespaces, 0, 3) === ['Tests', 'Fixtures', 'IndividualTheme']) {
            $fileName = implode('/', array_slice(explode('\\', $className), 3)) . '.php';
            require_once $this->getFixturePath("individual_theme/{$fileName}");
        } elseif (array_slice($namespaces, 0, 3) === ['Tests', 'Fixtures', 'AllTheme']) {
            $fileName = implode('/', array_slice(explode('\\', $className), 3)) . '.php';
            require_once $this->getFixturePath("all_theme/{$fileName}");
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
                    'Tests\Fixtures\IndividualTheme\Extend' => ['Tests\Fixtures\IndividualTheme\NoDependency'],
                    'Tests\Fixtures\IndividualTheme\NoDependency' => []
                ]
            ],
            'implement' => [
                $this->getFixturePath('/individual_theme/Implement.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Implement' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeInterface'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => []
                ]
            ],
            'use_trait' => [
                $this->getFixturePath('/individual_theme/UseTrait.php'),
                [
                    'Tests\Fixtures\IndividualTheme\UseTrait' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeTrait'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeTrait' => []
                ]
            ],
            'argument_type' => [
                $this->getFixturePath('/individual_theme/ArgumentType.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ArgumentType' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'argument_type_by_phpdoc' => [
                $this->getFixturePath('/individual_theme/ArgumentTypeByPhpDoc.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ArgumentTypeByPhpDoc' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => []
                ]
            ],
            'return_type' => [
                $this->getFixturePath('/individual_theme/ReturnType.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ReturnType' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'return_type_by_phpdoc' => [
                $this->getFixturePath('/individual_theme/ReturnTypeByPhpDoc.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ReturnTypeByPhpDoc' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'property' => [
                $this->getFixturePath('/individual_theme/Property.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Property' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'closure' => [
                $this->getFixturePath('/individual_theme/Closure.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Closure' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'function_call' => [
                $this->getFixturePath('/individual_theme/FunctionCall.php'),
                [
                    'Tests\Fixtures\IndividualTheme\FunctionCall' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass2'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => []
                ]
            ],
            'method_call' => [
                $this->getFixturePath('/individual_theme/MethodCall.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\MethodCall' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2']
                ]
            ],
            'property_fetch' => [
                $this->getFixturePath('/individual_theme/PropertyFetch.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\PropertyFetch' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass2', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'static_call' => [
                $this->getFixturePath('/individual_theme/StaticCall.php'),
                [
                    'Tests\Fixtures\IndividualTheme\StaticCall' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass3', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'class_const_call' => [
                $this->getFixturePath('/individual_theme/ClassConstCall.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ClassConstCall' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass3'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'static_property_fetch' => [
                $this->getFixturePath('/individual_theme/StaticPropertyFetch.php'),
                [
                    'Tests\Fixtures\IndividualTheme\StaticPropertyFetch' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass3', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => []
                ]
            ],
            'new_class' => [
                $this->getFixturePath('/individual_theme/NewClass.php'),
                [
                    'Tests\Fixtures\IndividualTheme\NewClass' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'instance_of' => [
                $this->getFixturePath('/individual_theme/InstanceOfClass.php'),
                [
                    'Tests\Fixtures\IndividualTheme\InstanceOfClass' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'catch_exception' => [
                $this->getFixturePath('/individual_theme/CatchException.php'),
                [
                    'Tests\Fixtures\IndividualTheme\CatchException' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeException'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeException' => []
                ]
            ],
            'object_array' => [
                $this->getFixturePath('/individual_theme/ObjectArray.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ObjectArray' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => []
                ]
            ],
            'array_dim_fetch' => [
                $this->getFixturePath('/individual_theme/ArrayDimFetch.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ArrayDimFetch' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass2', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'foreach_array' => [
                $this->getFixturePath('/individual_theme/ForeachArray.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ForeachArray' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass2', 'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'interface_extend' => [
                $this->getFixturePath('/individual_theme/InterfaceExtend.php'),
                [
                    'Tests\Fixtures\IndividualTheme\InterfaceExtend' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeInterface'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => []
                ]
            ],
            'abstract_extend' => [
                $this->getFixturePath('/individual_theme/AbstractExtend.php'),
                [
                    'Tests\Fixtures\IndividualTheme\AbstractExtend' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'abstract_implement' => [
                $this->getFixturePath('/individual_theme/AbstractImplement.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => [],
                    'Tests\Fixtures\IndividualTheme\AbstractImplement' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeInterface']
                ]
            ],
            'depend_on_comment' => [
                $this->getFixturePath('/individual_theme/DependOnComment.php'),
                [
                    'Tests\Fixtures\IndividualTheme\DependOnComment' => ['Tests\Fixtures\IndividualTheme\Foundations\SomeClass1'],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                ]
            ],
            'all_theme' => [
                $this->getFixturePath('/all_theme/AllTheme.php'),
                [
                    'Tests\Fixtures\AllTheme\AllTheme' => [
                        'Tests\Fixtures\AllTheme\Foundations\ParentClass',
                        'Tests\Fixtures\AllTheme\Foundations\SomeInterface',
                        'Tests\Fixtures\AllTheme\Foundations\SomeTrait',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass1',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass2',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass3',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass4',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass5',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass6',
                        'Tests\Fixtures\AllTheme\Foundations\SomeException1',
                        'Tests\Fixtures\AllTheme\Foundations\SomeException2',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass7',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass8',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass10',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass9',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass11',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass12',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass13',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass14',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass15',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass16',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass17',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass18',
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass19',
                    ],
                    'Tests\Fixtures\AllTheme\Foundations\ParentClass' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeInterface' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeTrait' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass3' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass4' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass5' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass6' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeException1' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeException2' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass7' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass8' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass10' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass9' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass11' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass12' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass13' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass14' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass15' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass16' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass17' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass18' => [],
                    'Tests\Fixtures\AllTheme\Foundations\SomeClass19' => [],
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

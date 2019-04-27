<?php
declare(strict_types=1);

namespace Tests\Integration;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraphBuilder;
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
                    'Tests\Fixtures\IndividualTheme\Extend' => [
                        'Tests\Fixtures\IndividualTheme\NoDependency' => ['extends']
                    ],
                    'Tests\Fixtures\IndividualTheme\NoDependency' => []
                ]
            ],
            'implement' => [
                $this->getFixturePath('/individual_theme/Implement.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Implement' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => ['implements']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => []
                ]
            ],
            'use_trait' => [
                $this->getFixturePath('/individual_theme/UseTrait.php'),
                [
                    'Tests\Fixtures\IndividualTheme\UseTrait' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeTrait' => ['use_trait']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeTrait' => []
                ]
            ],
            'argument_type' => [
                $this->getFixturePath('/individual_theme/ArgumentType.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ArgumentType' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'argument_type_by_phpdoc' => [
                $this->getFixturePath('/individual_theme/ArgumentTypeByPhpDoc.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ArgumentTypeByPhpDoc' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => []
                ]
            ],
            'return_type' => [
                $this->getFixturePath('/individual_theme/ReturnType.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ReturnType' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'return_type_by_phpdoc' => [
                $this->getFixturePath('/individual_theme/ReturnTypeByPhpDoc.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ReturnTypeByPhpDoc' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'property' => [
                $this->getFixturePath('/individual_theme/Property.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Property' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'closure' => [
                $this->getFixturePath('/individual_theme/Closure.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Closure' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['some_dependency'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => ['new:someMethod']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'function_call' => [
                $this->getFixturePath('/individual_theme/FunctionCall.php'),
                [
                    'Tests\Fixtures\IndividualTheme\FunctionCall' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => []
                ]
            ],
            'method_call' => [
                $this->getFixturePath('/individual_theme/MethodCall.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\MethodCall' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['new:testMethod', 'method_call:someMethod:testMethod'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['some_dependency']
                    ]
                ]
            ],
            'property_fetch' => [
                $this->getFixturePath('/individual_theme/PropertyFetch.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\PropertyFetch' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['new:someMethod', 'property_fetch:someProperty:someMethod'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'static_call' => [
                $this->getFixturePath('/individual_theme/StaticCall.php'),
                [
                    'Tests\Fixtures\IndividualTheme\StaticCall' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => ['method_call:someStaticMethod:someMethod'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'class_const_call' => [
                $this->getFixturePath('/individual_theme/ClassConstCall.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ClassConstCall' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => ['constant_fetch:SOME_CONST:someMethod']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'static_property_fetch' => [
                $this->getFixturePath('/individual_theme/StaticPropertyFetch.php'),
                [
                    'Tests\Fixtures\IndividualTheme\StaticPropertyFetch' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => ['property_fetch:someStatic:someMethod'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => []
                ]
            ],
            'new_class' => [
                $this->getFixturePath('/individual_theme/NewClass.php'),
                [
                    'Tests\Fixtures\IndividualTheme\NewClass' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['new:someMethod']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'instance_of' => [
                $this->getFixturePath('/individual_theme/InstanceOfClass.php'),
                [
                    'Tests\Fixtures\IndividualTheme\InstanceOfClass' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'catch_exception' => [
                $this->getFixturePath('/individual_theme/CatchException.php'),
                [
                    'Tests\Fixtures\IndividualTheme\CatchException' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeException' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeException' => []
                ]
            ],
            'object_array' => [
                $this->getFixturePath('/individual_theme/ObjectArray.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ObjectArray' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency', 'method_call:someMethod:testMethod'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => []
                ]
            ],
            'array_dim_fetch' => [
                $this->getFixturePath('/individual_theme/ArrayDimFetch.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ArrayDimFetch' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['new:testMethod', 'property_fetch:someProperty:testMethod', 'some_dependency'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['new:testMethod', 'method_call:someMethod:testMethod', 'some_dependency'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'foreach_array' => [
                $this->getFixturePath('/individual_theme/ForeachArray.php'),
                [
                    'Tests\Fixtures\IndividualTheme\ForeachArray' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => ['some_dependency', 'new:testMethod', 'property_fetch:someProperty:testMethod'],
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass2' => [],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass3' => []
                ]
            ],
            'interface_extend' => [
                $this->getFixturePath('/individual_theme/InterfaceExtend.php'),
                [
                    'Tests\Fixtures\IndividualTheme\InterfaceExtend' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => ['extends']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => []
                ]
            ],
            'abstract_extend' => [
                $this->getFixturePath('/individual_theme/AbstractExtend.php'),
                [
                    'Tests\Fixtures\IndividualTheme\AbstractExtend' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['extends']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => []
                ]
            ],
            'abstract_implement' => [
                $this->getFixturePath('/individual_theme/AbstractImplement.php'),
                [
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => [],
                    'Tests\Fixtures\IndividualTheme\AbstractImplement' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeInterface' => ['implements']
                    ]
                ]
            ],
            'depend_on_comment' => [
                $this->getFixturePath('/individual_theme/DependOnComment.php'),
                [
                    'Tests\Fixtures\IndividualTheme\DependOnComment' => [
                        'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => ['some_dependency']
                    ],
                    'Tests\Fixtures\IndividualTheme\Foundations\SomeClass1' => [],
                ]
            ],
            'unknown_dependency' => [
                $this->getFixturePath('/individual_theme/UnknownDependency.php'),
                [
                    'Tests\Fixtures\IndividualTheme\UnknownDependency' => [
                        'UnknownNew' => ['some_dependency'],
                        'UnknownProperty' => ['some_dependency'],
                        'UnknownMethod' => ['some_dependency'],
                        'UnknownClassMethod' => ['some_dependency']
                    ],
                    'UnknownNew' => [],
                    'UnknownProperty' => [],
                    'UnknownMethod' => [],
                    'UnknownClassMethod' => [],
                ]
            ],
            'all_theme' => [
                $this->getFixturePath('/all_theme/AllTheme.php'),
                [
                    'Tests\Fixtures\AllTheme\AllTheme' => [
                        'Tests\Fixtures\AllTheme\Foundations\ParentClass' => ['extends'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeInterface' => ['implements'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeTrait' => ['use_trait'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass1' => ['some_dependency', 'new:__construct', 'method_call:someMethod:testMethod1'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass2' => ['constant_fetch:STATUS_OK:__construct'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass3' => ['some_dependency', 'method_call:getUnknownClass:testMethod1'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass4' => ['some_dependency'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass5' => ['some_dependency', 'property_fetch:property:testMethod1', 'method_call:isStatusOk:testMethod1'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass6' => ['some_dependency', 'method_call:getSomeClass4:testMethod1'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeException1' => ['new:testMethod1'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeException2' => ['some_dependency'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass7' => ['some_dependency', 'method_call:someMethod:testMethod2'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass8' => ['some_dependency', 'method_call:someMethod:testMethod2'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass10' => ['some_dependency'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass9' => ['some_dependency'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass11' => ['method_call:someMethod:testMethod3'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass12' => ['some_dependency'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass13' => ['new:testMethod4', 'some_dependency', 'method_call:someMethod:testMethod4'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass14' => ['some_dependency'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass15' => ['new:testMethod5'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass16' => ['new:testMethod5', 'method_call:someMethod:testMethod5', 'some_dependency'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass17' => ['new:testMethod5'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass18' => ['some_dependency'],
                        'Tests\Fixtures\AllTheme\Foundations\SomeClass19' => ['some_dependency'],
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

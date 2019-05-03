<?php
declare(strict_types=1);

namespace Tests\Component;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\MethodCall;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\PropertyFetch;
use DependencyAnalyzer\DependencyGraph\DependencyTypes\SomeDependency;
use DependencyAnalyzer\Inspector\RuleViolationDetector;
use DependencyAnalyzer\Inspector\RuleViolationDetector\DependencyRuleFactory;
use DependencyAnalyzer\Inspector\Responses\VerifyDependencyResponse;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class InspectorTest extends TestCase
{
    public function provideCreate()
    {
        return [
            'depender(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['\Controller\\'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                []
            ],
            'depender(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['\Domain\\'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                [[
                    'dependerComponent' => 'ControllerLayer',
                    'depender' => '\Controller\Dir\Class2::callerMethod()',
                    'dependeeComponent' => 'ApplicationLayer',
                    'dependee' => '\Application\Class1::calleeMethod()',
                ]]
            ],
            'dependee(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'dependee' => ['\Domain\\'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                []
            ],
            'dependee(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'dependee' => ['\Controller\\'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                [[
                    'dependerComponent' => 'ApplicationLayer',
                    'depender' => '\Application\Dir\Class2::callerMethod()',
                    'dependeeComponent' => 'DomainLayer',
                    'dependee' => '\Domain\Class1::$calleeProperty',
                ]]
            ],
            'have other component(valid)' => [
                [
                    'Target' => [
                        'define' => ['\Application\Dir\Dir\Class3'],
                        'depender' => ['\Application\Class1', '\Application\Dir\Class2']
                    ],
                    'other' => [
                        'define' => ['\\', '!\Application\Dir\Dir\Class3'],
                    ]
                ],
                []
            ],
            'have other component(invalid)' => [
                [
                    'Target' => [
                        'define' => ['\Application\Dir\Dir\Class3'],
                        'depender' => ['\Application\Dir\Class2']
                    ],
                    'other' => [
                        'define' => ['\\', '!\Application\Dir\Dir\Class3'],
                    ]
                ],
                [[
                    'dependerComponent' => 'other',
                    'depender' => '\Application\Class1',
                    'dependeeComponent' => 'Target',
                    'dependee' => '\Application\Dir\Dir\Class3',
                ]]
            ],
            'have component name string(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['ControllerLayer'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                []
            ],
            'have component name string(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['DomainLayer'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                [[
                    'dependerComponent' => 'ControllerLayer',
                    'depender' => '\Controller\Dir\Class2::callerMethod()',
                    'dependeeComponent' => 'ApplicationLayer',
                    'dependee' => '\Application\Class1::calleeMethod()',
                ]]
            ],
            'exclude component name string(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['!DomainLayer'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                []
            ],
            'exclude component name string(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['!ControllerLayer'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                [[
                    'dependerComponent' => 'ControllerLayer',
                    'depender' => '\Controller\Dir\Class2::callerMethod()',
                    'dependeeComponent' => 'ApplicationLayer',
                    'dependee' => '\Application\Class1::calleeMethod()',
                ]]
            ],
            'restrict depender to property/method/class_constant(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['\Controller\Dir\Class2::callerMethod()'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                        'depender' => ['\Application\Dir\Class2::callerMethod()'],
                    ]
                ],
                []
            ],
            'restrict depender to property/method/class_constant(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['\Controller\Dir\Class2::$callerProperty'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                        'depender' => ['\Application\Dir\Class2::otherMethod()'],
                    ]
                ],
                [
                    [
                        'dependerComponent' => 'ControllerLayer',
                        'depender' => '\Controller\Dir\Class2::callerMethod()',
                        'dependeeComponent' => 'ApplicationLayer',
                        'dependee' => '\Application\Class1::calleeMethod()'
                    ],
                    [
                        'dependerComponent' => 'ApplicationLayer',
                        'depender' => '\Application\Dir\Class2::callerMethod()',
                        'dependeeComponent' => 'DomainLayer',
                        'dependee' => '\Domain\Class1::$calleeProperty'
                    ]
                ]
            ],
            'have public(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'public' => ['\Application\Class1'],
                        'depender' => ['ControllerLayer'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                        'public' => ['\Domain\Class1'],
                        'depender' => ['ApplicationLayer'],
                    ]
                ],
                []
            ],
            'have public(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'public' => ['\Application\Dir\Class2'],
                        'depender' => ['ControllerLayer'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                        'public' => ['\Domain\Dir\Class2'],
                        'depender' => ['ApplicationLayer'],
                    ]
                ],
                [
                    [
                        'dependerComponent' => 'ControllerLayer',
                        'depender' => '\Controller\Dir\Class2::callerMethod()',
                        'dependeeComponent' => 'ApplicationLayer',
                        'dependee' => '\Application\Class1::calleeMethod()'
                    ],
                    [
                        'dependerComponent' => 'ApplicationLayer',
                        'depender' => '\Application\Dir\Class2::callerMethod()',
                        'dependeeComponent' => 'DomainLayer',
                        'dependee' => '\Domain\Class1::$calleeProperty'
                    ]
                ]
            ],
            'have extra(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['!\\'],
                        'extra' => [
                            '\Application\Class1::calleeMethod()' => ['\Controller\Dir\Class2']
                        ],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                        'depender' => ['!\\'],
                        'extra' => [
                            '\Domain\Class1::$calleeProperty' => ['\Application\Dir\Class2']
                        ],
                    ]
                ],
                []
            ],
            'have extra(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['!\\'],
                        'extra' => [
                            '\Application\Class1::$calleeProperty' => ['\Controller\Dir\Class2']
                        ],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                        'depender' => ['!\\'],
                        'extra' => [
                            '\Domain\Class1::calleeMethod()' => ['\Application\Dir\Class2']
                        ],
                    ]
                ],
                [
                    [
                        'dependerComponent' => 'ControllerLayer',
                        'depender' => '\Controller\Dir\Class2::callerMethod()',
                        'dependeeComponent' => 'ApplicationLayer',
                        'dependee' => '\Application\Class1::calleeMethod()'
                    ],
                    [
                        'dependerComponent' => 'ApplicationLayer',
                        'depender' => '\Application\Dir\Class2::callerMethod()',
                        'dependeeComponent' => 'DomainLayer',
                        'dependee' => '\Domain\Class1::$calleeProperty'
                    ]
                ]
            ]
//            'exclude analysis list(valid)' => [
//                [
//                    'ControllerLayer' => [
//                        'define' => ['\Controller'],
//                    ],
//                    'ApplicationLayer' => [
//                        'define' => ['\Application'],
//                        'black' => ['ControllerLayer', 'DomainLayer'],
//                        'excludeAnalysis' => ['\Controller\Dir\Class2.php'],
//                    ],
//                    'DomainLayer' => [
//                        'define' => ['\Domain'],
//                    ]
//                ],
//                []
//            ],
//            'exclude analysis list(invalid)' => [
//                [
//                    'ControllerLayer' => [
//                        'define' => ['\Controller'],
//                        'exclude' => '\Controller\Providers'
//                    ],
//                    'ApplicationLayer' => [
//                        'define' => ['\Application'],
//                        'black' => ['ControllerLayer', 'DomainLayer'],
//                        'excludeAnalysis' => ['\Controller\Dir\Class1.php'],
//                    ],
//                    'DomainLayer' => [
//                        'define' => ['\Domain'],
//                    ]
//                ],
//                ['\Controller\Dir\Class2.php(ControllerLayer) must not depend on \Application\Class1.php(ApplicationLayer).']
//            ],
        ];
    }

    /**
     * @param array $ruleDefinition
     * @param array $expected
     * @dataProvider provideCreate
     */
    public function testCreate(array $ruleDefinition, array $expected)
    {
        $graph = $this->createDependencyGraph();
        $factory = new DependencyRuleFactory();
        $rules = $factory->create(['testCreateRule' => $ruleDefinition]);
        $detector = new RuleViolationDetector($rules);

        $actual = $detector->inspect($graph);

        $this->assertSame($expected, array_reduce($actual, function (array $result, VerifyDependencyResponse $response) {
            return array_merge($result, $response->getViolations());
        }, []));
    }

    protected function createDependencyGraph()
    {
        $graph = new Graph();

        $controller1 = $graph->createVertex('Controller\Class1');
        $controller2 = $graph->createVertex('Controller\Dir\Class2');
        $controller3 = $graph->createVertex('Controller\Dir\Dir\Class3');
        $application1 = $graph->createVertex('Application\Class1');
        $application2 = $graph->createVertex('Application\Dir\Class2');
        $application3 = $graph->createVertex('Application\Dir\Dir\Class3');
        $domain1 = $graph->createVertex('Domain\Class1');
        $domain2 = $graph->createVertex('Domain\Dir\Class2');
        $domain3 = $graph->createVertex('Domain\Dir\Dir\Class3');
        $carbon = $graph->createVertex('Carbon\Carbon');

        $controller1->createEdgeTo($controller2)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $controller1->createEdgeTo($controller3)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $controller2->createEdgeTo($controller3)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $controller2->createEdgeTo($application1)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new MethodCall('calleeMethod', 'callerMethod')]);
        $controller3->createEdgeTo($carbon)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $application1->createEdgeTo($application2)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $application1->createEdgeTo($application3)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $application2->createEdgeTo($application3)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $application2->createEdgeTo($domain1)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new PropertyFetch('calleeProperty', 'callerMethod')]);
        $application3->createEdgeTo($carbon)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $domain1->createEdgeTo($domain2)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $domain1->createEdgeTo($domain3)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $domain2->createEdgeTo($domain3)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);
        $domain3->createEdgeTo($carbon)
            ->setAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY, [new SomeDependency()]);

//        $types = $edge->getAttribute(DependencyGraph::DEPENDENCY_TYPE_KEY) ?? [];

        return new DependencyGraph($graph);
    }

    public function provideCreate_WhenInvalidRuleDefinition()
    {
        return [
            'invalid_group_name' => [
                [
                    'controller' => [
                        'define' => '\Controller',
                    ],
                    'ApplicationLayer' => [
                        'define' => '\Application',
                    ],
                ]
            ],
            'no_define' => [
                [
                    'ControllerLayer' => [
                        'define' => '\Controller',
                    ],
                    'ApplicationLayer' => [
                    ],
                ]
            ],
        ];
    }

    /**
     * @param array $ruleDefinitions
     * @dataProvider provideCreate_WhenInvalidRuleDefinition
     * @expectedException \DependencyAnalyzer\Exceptions\InvalidRuleDefinition
     */
    public function testCreate_WhenInvalidRuleDefinition(array $ruleDefinitions)
    {
        $factory = new DependencyRuleFactory();
        $factory->create($ruleDefinitions);
    }

    public function provideToArray()
    {
        $depnderInvalid = [
            'ControllerLayer' => [
                'define' => ['\\Controller\\'],
            ],
            'ApplicationLayer' => [
                'define' => ['\\Application\\'],
                'depender' => ['\\Domain\\'],
            ],
            'DomainLayer' => [
                'define' => ['\\Domain\\'],
            ]
        ];
        return [
            'basic' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['\Controller\\'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                [
                    'ControllerLayer' => [
                        'define' => [
                            'include' => ['\Controller\\'],
                            'exclude' => []
                        ],
                    ],
                    'ApplicationLayer' => [
                        'define' => [
                            'include' => ['\Application\\'],
                            'exclude' => []
                        ],
                        'depender' => [
                            'include' => ['\Controller\\'],
                            'exclude' => [],
                        ],
                    ],
                    'DomainLayer' => [
                        'define' => [
                            'include' => ['\Domain\\'],
                            'exclude' => []
                        ],
                    ]
                ]
            ],
            'have exclude' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\', '!\Application\Providers\\'],
                        'depender' => ['!\Controller\\'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                [
                    'ControllerLayer' => [
                        'define' => [
                            'include' => ['\Controller\\'],
                            'exclude' => []
                        ],
                    ],
                    'ApplicationLayer' => [
                        'define' => [
                            'include' => ['\Application\\'],
                            'exclude' => ['\Application\Providers\\']
                        ],
                        'depender' => [
                            'include' => [],
                            'exclude' => ['\Controller\\'],
                        ],
                    ],
                    'DomainLayer' => [
                        'define' => [
                            'include' => ['\Domain\\'],
                            'exclude' => []
                        ],
                    ]
                ]
            ],
            'have component name string' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller\\'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application\\'],
                        'depender' => ['ControllerLayer'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain\\'],
                    ]
                ],
                [
                    'ControllerLayer' => [
                        'define' => [
                            'include' => ['\Controller\\'],
                            'exclude' => []
                        ],
                    ],
                    'ApplicationLayer' => [
                        'define' => [
                            'include' => ['\Application\\'],
                            'exclude' => []
                        ],
                        'depender' => [
                            'include' => ['\Controller\\'],
                            'exclude' => []
                        ],
                    ],
                    'DomainLayer' => [
                        'define' => [
                            'include' => ['\Domain\\'],
                            'exclude' => []
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider provideToArray
     * @param array $ruleDefinition
     * @param array $expected
     */
    public function testToArray(array $ruleDefinition, array $expected)
    {
        $factory = new DependencyRuleFactory();
        $rules = $factory->create(['testCreateRule' => $ruleDefinition]);

        $this->assertCount(1, $rules);
        $this->assertEquals($expected, $rules[0]->toArray()['testCreateRule']);
    }
}

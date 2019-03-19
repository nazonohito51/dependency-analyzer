<?php
declare(strict_types=1);

namespace Tests\Integration;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRuleFactory;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class DependencyRuleTest extends TestCase
{
    public function provideCreate()
    {
        return [
            'depender(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application'],
                        'depender' => ['\Controller'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain'],
                    ]
                ],
                []
            ],
            'depender(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application'],
                        'depender' => ['\Domain'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain'],
                    ]
                ],
                ['Controller\Dir\Class2(ControllerLayer) must not depend on Application\Class1(ApplicationLayer).']
            ],
            'dependee(valid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application'],
                        'dependee' => ['\Domain'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain'],
                    ]
                ],
                []
            ],
            'dependee(invalid)' => [
                [
                    'ControllerLayer' => [
                        'define' => ['\Controller'],
                    ],
                    'ApplicationLayer' => [
                        'define' => ['\Application'],
                        'dependee' => ['\Controller'],
                    ],
                    'DomainLayer' => [
                        'define' => ['\Domain'],
                    ]
                ],
                ['Application\Dir\Class2(ApplicationLayer) must not depend on Domain\Class1(DomainLayer).']
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
                ['Application\Class1(other) must not depend on Application\Dir\Dir\Class3(Target).']
            ],
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
        $rules = $factory->create([$ruleDefinition]);

        $actual = $rules[0]->isSatisfyBy($graph);

        $this->assertSame($expected, $actual);
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

        $controller1->createEdgeTo($controller2);
        $controller1->createEdgeTo($controller3);
        $controller2->createEdgeTo($controller3);
        $controller2->createEdgeTo($application1);
        $controller3->createEdgeTo($carbon);
        $application1->createEdgeTo($application2);
        $application1->createEdgeTo($application3);
        $application2->createEdgeTo($application3);
        $application2->createEdgeTo($domain1);
        $application3->createEdgeTo($carbon);
        $domain1->createEdgeTo($domain2);
        $domain1->createEdgeTo($domain3);
        $domain2->createEdgeTo($domain3);
        $domain3->createEdgeTo($carbon);

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
}

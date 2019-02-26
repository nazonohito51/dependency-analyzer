<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\DirectedGraph;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class DependencyRuleTest extends TestCase
{
    public function provideIsSatisfiedBy()
    {
        return [
            'white list(valid)' => [
                [
                    'Controller' => [
                        'define' => '/Controller',
                    ],
                    'Application' => [
                        'define' => '/Application',
                        'white' => ['Controller'],
                    ],
                    'Domain' => [
                        'define' => '/Domain',
                    ]
                ],
                []
            ],
            'white list(invalid)' => [
                [
                    'Controller' => [
                        'define' => '/Controller',
                    ],
                    'Application' => [
                        'define' => '/Application',
                        'white' => ['Domain'],
                    ],
                    'Domain' => [
                        'define' => '/Domain',
                    ]
                ],
                ['/Controller/Dir/Class2.php(Controller) must not depend on /Application/Class1.php(Application).']
            ],
            'black list(valid)' => [
                [
                    'Controller' => [
                        'define' => '/Controller',
                    ],
                    'Application' => [
                        'define' => '/Application',
                        'black' => ['Domain'],
                    ],
                    'Domain' => [
                        'define' => '/Domain',
                    ]
                ],
                []
            ],
            'black list(invalid)' => [
                [
                    'Controller' => [
                        'define' => '/Controller',
                    ],
                    'Application' => [
                        'define' => '/Application',
                        'black' => ['Controller'],
                    ],
                    'Domain' => [
                        'define' => '/Domain',
                    ]
                ],
                ['/Controller/Dir/Class2.php(Controller) must not depend on /Application/Class1.php(Application).']
            ],
            'exclude analysis list(valid)' => [
                [
                    'Controller' => [
                        'define' => '/Controller',
                    ],
                    'Application' => [
                        'define' => '/Application',
                        'black' => ['Controller', 'Domain'],
                        'excludeAnalysis' => ['/Controller/Dir/Class2.php'],
                    ],
                    'Domain' => [
                        'define' => '/Domain',
                    ]
                ],
                []
            ],
            'exclude analysis list(invalid)' => [
                [
                    'Controller' => [
                        'define' => '/Controller',
                    ],
                    'Application' => [
                        'define' => '/Application',
                        'black' => ['Controller', 'Domain'],
                        'excludeAnalysis' => ['/Controller/Dir/Class1.php'],
                    ],
                    'Domain' => [
                        'define' => '/Domain',
                    ]
                ],
                ['/Controller/Dir/Class2.php(Controller) must not depend on /Application/Class1.php(Application).']
            ],
        ];
    }

    /**
     * @param array $ruleDefinition
     * @param array $expected
     * @dataProvider provideIsSatisfiedBy
     */
    public function testIsSatisfiedBy(array $ruleDefinition, array $expected)
    {
        $graph = $this->createDirectedGraph();
        $rule = new DependencyRule($ruleDefinition);

        $errors = $rule->isSatisfyBy($graph);

        $this->assertEquals($expected, $errors);
    }

    protected function createDirectedGraph()
    {
        // TODO: remove dependency to Graph
        $graph = new Graph();

        $controller1 = $graph->createVertex('/Controller/Class1.php');
        $controller2 = $graph->createVertex('/Controller/Dir/Class2.php');
        $controller3 = $graph->createVertex('/Controller/Dir/Dir/Class3.php');
        $application1 = $graph->createVertex('/Application/Class1.php');
        $application2 = $graph->createVertex('/Application/Dir/Class2.php');
        $application3 = $graph->createVertex('/Application/Dir/Dir/Class3.php');
        $domain1 = $graph->createVertex('/Domain/Class1.php');
        $domain2 = $graph->createVertex('/Domain/Dir/Class2.php');
        $domain3 = $graph->createVertex('/Domain/Dir/Dir/Class3.php');
        $carbon = $graph->createVertex('/Carbon/Carbon.php');

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

        return new DirectedGraph($graph);
    }
}

<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph;
use Fhaculty\Graph\Graph;
use Tests\TestCase;

class DependencyRuleTest extends TestCase
{
    public function provideIsSatisfiedBy()
    {
        return [
            'white list(valid)' => [
                [
                    '@controller' => [
                        'define' => ['\Controller'],
                    ],
                    '@application' => [
                        'define' => ['\Application'],
                        'white' => ['@controller'],
                    ],
                    '@domain' => [
                        'define' => ['\Domain'],
                    ]
                ],
                []
            ],
            'white list(invalid)' => [
                [
                    '@controller' => [
                        'define' => ['\Controller'],
                    ],
                    '@application' => [
                        'define' => ['\Application'],
                        'white' => ['@domain'],
                    ],
                    '@domain' => [
                        'define' => ['\Domain'],
                    ]
                ],
                ['Controller\Dir\Class2(@controller) must not depend on Application\Class1(@application).']
            ],
            'black list(valid)' => [
                [
                    '@controller' => [
                        'define' => ['\Controller'],
                    ],
                    '@application' => [
                        'define' => ['\Application'],
                        'black' => ['@domain'],
                    ],
                    '@domain' => [
                        'define' => ['\Domain'],
                    ]
                ],
                []
            ],
            'black list(invalid)' => [
                [
                    '@controller' => [
                        'define' => ['\Controller'],
                    ],
                    '@application' => [
                        'define' => ['\Application'],
                        'black' => ['@controller'],
                    ],
                    '@domain' => [
                        'define' => ['\Domain'],
                    ]
                ],
                ['Controller\Dir\Class2(@controller) must not depend on Application\Class1(@application).']
            ],
//            'exclude analysis list(valid)' => [
//                [
//                    '@controller' => [
//                        'define' => ['\Controller'],
//                    ],
//                    '@application' => [
//                        'define' => ['\Application'],
//                        'black' => ['@controller', '@domain'],
//                        'excludeAnalysis' => ['\Controller\Dir\Class2.php'],
//                    ],
//                    '@domain' => [
//                        'define' => ['\Domain'],
//                    ]
//                ],
//                []
//            ],
//            'exclude analysis list(invalid)' => [
//                [
//                    '@controller' => [
//                        'define' => ['\Controller'],
//                        'exclude' => '\Controller\Providers'
//                    ],
//                    '@application' => [
//                        'define' => ['\Application'],
//                        'black' => ['@controller', '@domain'],
//                        'excludeAnalysis' => ['\Controller\Dir\Class1.php'],
//                    ],
//                    '@domain' => [
//                        'define' => ['\Domain'],
//                    ]
//                ],
//                ['\Controller\Dir\Class2.php(@controller) must not depend on \Application\Class1.php(@application).']
//            ],
        ];
    }

    /**
     * @param array $ruleDefinition
     * @param array $expected
     * @dataProvider provideIsSatisfiedBy
     */
    public function testIsSatisfiedBy(array $ruleDefinition, array $expected)
    {
        $graph = $this->createDependencyGraph();
        $rule = new DependencyRule($ruleDefinition);

        $errors = $rule->isSatisfyBy($graph);

        $this->assertEquals($expected, $errors);
    }

    protected function createDependencyGraph()
    {
        // TODO: remove dependency to Graph
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

    public function testGetDefinition()
    {
        $definition = [
            '@controller' => [
                'define' => ['\Controller'],
            ],
            '@application' => [
                'define' => ['\Application'],
                'white' => ['@controller'],
            ],
            '@domain' => [
                'define' => ['\Domain'],
            ]
        ];
        $rule = new DependencyRule($definition);

        $this->assertEquals($definition, $rule->getDefinition());
    }
}

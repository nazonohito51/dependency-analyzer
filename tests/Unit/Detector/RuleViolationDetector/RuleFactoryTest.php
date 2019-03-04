<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRule;
use DependencyAnalyzer\Detector\RuleViolationDetector\RuleFactory;
use DependencyAnalyzer\Exceptions\InvalidRuleDefinition;
use Tests\TestCase;

class RuleFactoryTest extends TestCase
{
    public function provideCreate()
    {
        $definition1 = [
            [
                '@controller' => [
                    'define' => '\Controller',
                ],
                '@application' => [
                    'define' => '\Application',
                    'white' => ['@controller'],
                ],
                '@domain' => [
                    'define' => '\Domain',
                ]
            ]
        ];

        return [
            [$definition1, $definition1],
        ];
    }

    public function testCreate()
    {
        $ruleDefinitions = [
            [
                '@controller' => [
                    'define' => '\Controller',
                ],
                '@application' => [
                    'define' => '\Application',
                    'white' => ['@controller'],
                ],
                '@domain' => [
                    'define' => '\Domain',
                ]
            ],
            [
                '@entities' => [
                    'define' => '\Domain\Entities',
                    'white' => ['@repositoreis']
                ],
                '@repositories' => [
                    'define' => '\Domain\Repositories',
                    'white' => ['@controller'],
                ]
            ],
        ];
        $factory = new RuleFactory($ruleDefinitions);

        $rules = $factory->create();

        $this->assertCount(2, $rules);
        $this->assertEquals($ruleDefinitions, array_map(function (DependencyRule $rule) {
            return $rule->getDefinition();
        }, $rules));
    }

    public function provideCreate_WhenInvalidRuleDefinition()
    {
        return [
            'invalid_group_name' => [
                [
                    'controller' => [
                        'define' => '\Controller',
                    ],
                    '@application' => [
                        'define' => '\Application',
                    ],
                ]
            ],
            'no_define' => [
                [
                    '@controller' => [
                        'define' => '\Controller',
                    ],
                    '@application' => [
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
        $factory = new RuleFactory($ruleDefinitions);

        $factory->create();
    }
}

<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRule;
use DependencyAnalyzer\Detector\RuleViolationDetector\DependencyRuleFactory;
use DependencyAnalyzer\Exceptions\InvalidRuleDefinition;
use Tests\TestCase;

class DependencyRuleFactoryTest extends TestCase
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
            [
                '@entities' => [
                    'define' => ['\Domain\Entities'],
                    'white' => ['@repositories']
                ],
                '@repositories' => [
                    'define' => ['\Domain\Repositories']
                ]
            ],
        ];
        $factory = new DependencyRuleFactory();

        $rules = $factory->create($ruleDefinitions);

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
        $factory = new DependencyRuleFactory();

        $factory->create($ruleDefinitions);
    }
}

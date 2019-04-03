<?php
$commandsComponent = '\DependencyAnalyzer\Commands\\';
$graphBuilderComponent = '\DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder';
$unknownClassReflection = '\DependencyAnalyzer\DependencyDumper\UnknownClassReflection';

return [
    'my dependency rule' => [
        'commands' => [
            'define' => [$commandsComponent],
            'description' => 'CLI application commands. control CLI input/output.',
            'depender' => ['!\DependencyAnalyzer\\'],
        ],
        'dependency_dumper' => [
            'define' => ['\DependencyAnalyzer\DependencyDumper', '\DependencyAnalyzer\DependencyDumper\\', "!{$unknownClassReflection}"],
            'description' => ['Analyze repository, and build DependencyGraph'],
            'depender' => ['commands']
        ],
        'unknown class reflection' => [             // I will remove this component............
            'define' => [$unknownClassReflection],
            'description' => 'temporary component........',
            'depender' => ['dependency_dumper', 'graph_builder']
        ],
        'graph_builder' => [                        // I'm thinking about this component position.
            'define' => [$graphBuilderComponent],
            'description' => ['Build DependencyGraph'],
            'depender' => ['dependency_dumper']
        ],
        'dependency_graph' => [
            'define' => ['\DependencyAnalyzer\DependencyGraph', '\DependencyAnalyzer\DependencyGraph\\', "!{$graphBuilderComponent}"],
            'description' => 'Graph of dependencies between classes. Core of this library.',
            'depender' => ['commands', 'dependency_dumper', 'graph_builder', 'inspectors']
        ],
        'inspectors' => [
            'define' => ['\DependencyAnalyzer\Inspector\\'],
            'description' => 'Inspect DependencyGraph.',
            'depender' => ['commands']
        ],
        'patterns' => [
            'define' => ['\DependencyAnalyzer\Patterns\\'],
            'description' => 'Name patterns'
        ],
        'exceptions' => [
            'define' => ['\DependencyAnalyzer\Exceptions\\'],
            'dependee' => ['@php_native']
        ]
    ],
    'relation of external libraries' => [
        'commands' => [
            'define' => [$commandsComponent],
        ],
        'dependency_dumper' => [
            'define' => ['\DependencyAnalyzer\DependencyDumper', '\DependencyAnalyzer\DependencyDumper\\'],
        ],
        'dependency_graph' => [
            'define' => ['\DependencyAnalyzer\DependencyGraph', '\DependencyAnalyzer\DependencyGraph\\']
        ],
        'inspectors' => [
            'define' => ['\DependencyAnalyzer\Inspector\\'],
        ],
        'other' => [
            'define' => ['\DependencyAnalyzer\\', "!{$commandsComponent}", '!\DependencyAnalyzer\DependencyGraph', '!\DependencyAnalyzer\DependencyGraph\\', '!\DependencyAnalyzer\DependencyDumper\\', '!\DependencyAnalyzer\Exceptions\\', '!\DependencyAnalyzer\Inspector\\']
        ],
        'Symfony Console' => [
            'define' => ['\Symfony\Component\Console\\'],
            'depender' => ['commands']
        ],
        'PHPStan' => [
            'define' => ['\PHPStan\\'],
            'depender' => ['dependency_dumper', 'dependency_graph']
        ],
        'PHP Parser' => [
            'define' => ['\PhpParser\\'],
            'depender' => ['dependency_dumper']
        ],
        'graph' => [
            'define' => ['\Fhaculty\Graph\\'],
            'depender' => ['dependency_graph', 'inspectors']        // I will remove inspectors...
        ]
    ]
];

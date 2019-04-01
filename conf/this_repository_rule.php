<?php
$unknownClassReflection = '\DependencyAnalyzer\DependencyDumper\UnknownClassReflection';

return [
    'my dependency rule' => [
        'commands' => [
            'define' => ['\DependencyAnalyzer\Commands\\'],
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
            'define' => ['\DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder'],
            'description' => ['Build DependencyGraph'],
            'depender' => ['dependency_dumper']
        ],
        'dependency_graph' => [
            'define' => ['\DependencyAnalyzer\DependencyGraph', '\DependencyAnalyzer\DependencyGraph\\', '!\DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder'],
            'description' => 'Graph of dependencies between classes. Core of this library.',
            'depender' => ['commands', 'dependency_dumper', 'graph_builder', 'detectors']
        ],
        'detectors' => [
            'define' => ['\DependencyAnalyzer\Detector\\'],
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
    ]
];

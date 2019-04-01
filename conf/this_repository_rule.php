<?php

return [
    'my dependency rule' => [
        'commands' => [
            'define' => ['\DependencyAnalyzer\Commands\\'],
            'description' => 'CLI application commands. control CLI input/output.',
            'depender' => ['!\DependencyAnalyzer\\']
        ],
        'dependency_dumper' => [
            'define' => ['\DependencyAnalyzer\DependencyDumper', '\DependencyAnalyzer\DependencyDumper\\'],
            'description' => ['analyze repository, and build DependencyGraph'],
            'depender' => ['commands']
        ],
        'graph_builder' => [
            'define' => ['\DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder'],
            'description' => ['build DependencyGraph'],
            'depender' => ['dependency_dumper']
        ],
        'dependency_graph' => [
            'define' => ['\DependencyAnalyzer\DependencyGraph', '\DependencyAnalyzer\DependencyGraph\\', '!\DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder'],
            'description' => 'Graph of dependency between classes. Core of this library.',
            'depender' => ['commands', 'graph_builder', 'detectors']
        ],
        'detectors' => [
            'define' => ['\DependencyAnalyzer\Detector\\'],
            'description' => 'inspect DependencyGraph.',
            'depender' => ['commands']
        ],
        'patterns' => [
            'define' => ['\DependencyAnalyzer\Patterns\\'],
            'description' => 'name patterns'
        ],
        'exceptions' => [
            'define' => ['\DependencyAnalyzer\Exceptions\\'],
            'dependee' => ['@php_native']
        ]
    ]
];

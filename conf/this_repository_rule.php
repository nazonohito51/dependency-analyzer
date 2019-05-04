<?php

return [
    'my dependency rule' => [
        'commands' => [
            'define' => ['\DependencyAnalyzer\Commands\\'],
            'description' => 'CLI application commands. Control CLI input/output.',
            'public' => [],
            'depender' => ['!\DependencyAnalyzer\\'],
            'graph' => ['namespace', 'folding', 'description']
        ],
        'dependency_dumper' => [
            'define' => ['\DependencyAnalyzer\DependencyDumper', '\DependencyAnalyzer\DependencyDumper\\'],
            'description' => ['Analyze repository, and build DependencyGraph'],
            'public' => ['\DependencyAnalyzer\DependencyDumper', '\DependencyAnalyzer\DependencyDumper\ObserverInterface'],
            'depender' => ['commands'],
            'graph' => ['namespace', 'folding', 'description']
        ],
        'graph_builder' => [
            'define' => ['\DependencyAnalyzer\DependencyGraphBuilder', '\DependencyAnalyzer\DependencyGraphBuilder\\'],
            'description' => ['Build DependencyGraph'],
            'public' => ['\DependencyAnalyzer\DependencyGraphBuilder', '\DependencyAnalyzer\DependencyGraphBuilder\ObserverInterface'],
            'depender' => ['dependency_dumper'],
            'graph' => ['namespace', 'folding', 'description']
        ],
        'dependency_graph' => [
            'define' => ['\DependencyAnalyzer\DependencyGraph', '\DependencyAnalyzer\DependencyGraph\\'],
            'description' => 'Graph of dependencies between classes. Core of this library.',
            'depender' => ['commands', 'dependency_dumper', 'graph_builder', 'inspectors'],
            'graph' => ['namespace', 'folding', 'description']
        ],
        'inspectors' => [
            'define' => ['\DependencyAnalyzer\Inspector\\'],
            'description' => 'Inspect DependencyGraph.',
            'depender' => ['commands'],
            'graph' => ['namespace', 'folding', 'description']
        ],
        'exceptions' => [
            'define' => ['\DependencyAnalyzer\Exceptions\\'],
            'dependee' => ['@php_native', 'PHPParser', 'graph'],
            'graph' => ['folding']
        ],
        'SymfonyConsole' => [
            'define' => ['\Symfony\Component\Console\\'],
            'depender' => ['commands'],
            'graph' => ['folding']
        ],
        'PHPStan' => [
            'define' => ['\PHPStan\\'],
            'depender' => ['dependency_dumper', 'graph_builder', 'dependency_graph'],
            'graph' => ['folding']
        ],
        'PHPParser' => [
            'define' => ['\PhpParser\\'],
            'depender' => ['dependency_dumper', 'exceptions'],
            'graph' => ['folding']
        ],
        'graph' => [
            'define' => ['\Fhaculty\Graph\\'],
            'depender' => ['graph_builder', 'dependency_graph', 'inspectors', 'exceptions'],        // I will remove inspectors...
            'graph' => ['folding']
        ]
    ]
];

<?php

return [
    'my dependency rule' => [
        'commands' => [
            'define' => ['\DependencyAnalyzer\Commands\\'],
            'description' => 'CLI application commands. control CLI input/output.',
            'depender' => ['!\DependencyAnalyzer\\'],
        ],
        'dependency_dumper' => [
            'define' => ['\DependencyAnalyzer\DependencyDumper', '\DependencyAnalyzer\DependencyDumper\\'],
            'description' => ['Analyze repository, and build DependencyGraph'],
            'depender' => ['commands']
        ],
        'graph_builder' => [
            'define' => ['\DependencyAnalyzer\DependencyGraphBuilder'],
            'description' => ['Build DependencyGraph'],
            'depender' => ['dependency_dumper']
        ],
        'dependency_graph' => [
            'define' => ['\DependencyAnalyzer\DependencyGraph', '\DependencyAnalyzer\DependencyGraph\\'],
            'description' => 'Graph of dependencies between classes. Core of this library.',
            'depender' => ['commands', 'dependency_dumper', 'graph_builder', 'inspectors']
        ],
        'inspectors' => [
            'define' => ['\DependencyAnalyzer\Inspector\\'],
            'description' => 'Inspect DependencyGraph.',
            'depender' => ['commands']
        ],
        'matchers' => [
            'define' => ['\DependencyAnalyzer\Matcher\\'],
            'description' => 'Matcher of class name and rule definition',
            'depender' => ['dependency_graph', 'inspectors']
        ],
//        'exceptions' => [
//            'define' => ['\DependencyAnalyzer\Exceptions\\'],
//            'dependee' => ['@php_native']
//        ],
        'Symfony Console(external)' => [
            'define' => ['\Symfony\Component\Console\\'],
            'depender' => ['commands']
        ],
        'PHPStan(external)' => [
            'define' => ['\PHPStan\\'],
            'depender' => ['dependency_dumper', 'graph_builder', 'dependency_graph']
        ],
        'PHP Parser(external)' => [
            'define' => ['\PhpParser\\'],
            'depender' => ['dependency_dumper']
        ],
        'graph(external)' => [
            'define' => ['\Fhaculty\Graph\\'],
            'depender' => ['graph_builder', 'dependency_graph', 'inspectors']        // I will remove inspectors...
        ]
    ]
];

<?php
return [
    'namespace' => [
        'Commands' => ['\DependencyAnalyzer\Commands'],
        'DependencyDumper' => ['\DependencyAnalyzer\DependencyDumper'],
        'DependencyGraph' => ['\DependencyAnalyzer\DependencyGraph'],
        'Detector' => ['\DependencyAnalyzer\Detector'],
        'PHPStan' => ['\PHPStan'],
        'Graph' => ['\Fhaculty']
    ],
    'group' => [
//        'DependencyAnalyzer\Commands' => ['\DependencyAnalyzer\Commands'],
        'DependencyAnalyzer\DependencyDumper' => ['\DependencyAnalyzer\DependencyDumper'],
        'DependencyAnalyzer\DependencyGraph' => ['\DependencyAnalyzer\DependencyGraph'],
        'DependencyAnalyzer\Detector' => ['\DependencyAnalyzer\Detector'],
        'PHPStan' => ['\PHPStan'],
        'Fhaculty' => ['\Fhaculty'],
        'Symfony\Component\Console' => ['\Symfony\Component\Console'],
    ],
    'exclude' => ['\PhpParser\Node', '\DependencyAnalyzer\Exceptions', '@php_native'],
];

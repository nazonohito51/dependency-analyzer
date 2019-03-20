<?php
return [
    'namespace' => [
        'Commands' => ['\DependencyAnalyzer\Commands'],
        'DependencyDumper' => ['\DependencyAnalyzer\DependencyDumper'],
        'DependencyGraph' => ['\DependencyAnalyzer\DependencyGraph'],
        'Detector' => ['\DependencyAnalyzer\Detector'],
        'Exceptions' => ['\DependencyAnalyzer\Exceptions'],
        'PHPStan' => ['\PHPStan']
    ],
    'exclude' => ['\PhpParser\Node'],
];

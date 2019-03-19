<?php
$controllerDefine = ['\App'];
$applicationDefine = ['\Acme\Application'];
$domainDefine = ['\Acme\Domain'];
$repositoryDefine = ['\Acme\Domain\Repositories'];

return [
    'layer dependency rule' => [
        '@controller' => [
            'define' => $controllerDefine,
        ],
        '@application' => [
            'define' => $applicationDefine,
            'white' => $controllerDefine,
        ],
        '@domain' => [
            'define' => $domainDefine,
            'white' => $applicationDefine
        ]
    ],
    'create entity rule' => [
        '@entities' => [
            'define' => ['\Acme\Domain\Entities'],
            'white' => $repositoryDefine
        ],
        '@repositories' => [
            'define' => $repositoryDefine,
        ]
    ],
];

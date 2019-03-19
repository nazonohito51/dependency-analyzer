<?php
$controllerDefine = ['\App'];
$applicationDefine = ['\Acme\Application'];
$domainDefine = ['\Acme\Domain'];
$repositoryDefine = ['\Acme\Domain\Repositories'];

return [
    'layer dependency rule' => [
        'ControllerLayer' => [
            'define' => $controllerDefine,
        ],
        'ApplicationLayer' => [
            'define' => $applicationDefine,
            'white' => $controllerDefine,
        ],
        'DomainLayer' => [
            'define' => $domainDefine,
            'white' => $applicationDefine
        ]
    ],
    'create entity rule' => [
        'Entities' => [
            'define' => ['\Acme\Domain\Entities'],
            'white' => $repositoryDefine
        ],
        'Repositories' => [
            'define' => $repositoryDefine,
        ]
    ],
];

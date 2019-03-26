<?php
$controllerDefine = ['\App', '!\App\Providers'];
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
            'depender' => $controllerDefine,
        ],
        'DomainLayer' => [
            'define' => $domainDefine,
            'depender' => $applicationDefine
        ]
    ],
    'create entity rule' => [
        'Entities' => [
            'define' => ['\Acme\Domain\Entities'],
            'depender' => $repositoryDefine
        ],
        'Repositories' => [
            'define' => $repositoryDefine,
        ]
    ],
];

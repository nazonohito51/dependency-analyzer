<?php
$controllerDefine = ['\App\\', '!\App\Providers\\'];
$applicationDefine = ['\Acme\Application\\'];
$domainDefine = ['\Acme\Domain\\'];
$repositoryDefine = ['\Acme\Domain\Repositories\\'];

return [
    'Layer dependency rule' => [
        'ControllerLayer' => [
            'define' => $controllerDefine,
        ],
        'ApplicationLayer' => [
            'define' => $applicationDefine,
            'depender' => ['ControllerLayer'],
        ],
        'DomainLayer' => [
            'define' => $domainDefine,
            'depender' => ['ApplicationLayer']
        ]
    ],
    'Facade police' => [
        'Sanctuary' => [
            'define' => array_merge($controllerDefine, $applicationDefine, $domainDefine),
            'dependee' => ['!Facade']
        ],
        'Facade' => [
            'define' => ['\App','\Artisan','\Auth','\Blade','\Broadcast','\Bus','\Cache','\Config','\Cookie','\Crypt','\DB','\Event','\File','\Gate','\Hash','\Lang','\Log','\Mail','\Notification','\Password','\Queue','\Redirect','\Request','\Response','\Route','\Schema','\Session','\Storage','\URL','\Validator','\View']
        ]
    ]
];

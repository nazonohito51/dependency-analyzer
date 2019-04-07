<?php

return [
    'Your graph rule' => [
        'ControllerLayer' => [
            'define' => ['\App\\', '!\App\Providers\\'],
            'graph' => ['folding']
        ],
        'ApplicationLayer' => [
            'define' => ['\Acme\Application\\'],
            'graph' => ['namespace']
        ],
        'DomainLayer' => [
            'define' => ['\Acme\Domain\\'],
            'graph' => ['namespace']
        ],
        'Providers' => [
            'define' => ['\App\Providers\\'],
            'graph' => ['folding']
        ],
        'Illuminate' => [
            'define' => ['\Illuminate\\'],
            'graph' => ['folding']
        ]
    ]
];

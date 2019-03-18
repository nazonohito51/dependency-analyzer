<?php
return [
    'layer dependency rule' => [
        '@controller' => [
            'define' => ['\App'],
        ],
        '@application' => [
            'define' => ['\Acme\Application'],
            'white' => ['@controller'],
        ],
        '@domain' => [
            'define' => ['\Acme\Domain'],
            'white' => ['@application']
        ]
    ],
    'create entity rule' => [
        '@entities' => [
            'define' => ['\Acme\Domain\Entities'],
            'white' => ['@repositories']
        ],
        '@repositories' => [
            'define' => ['\Acme\Domain\Repositories'],
        ]
    ],
];

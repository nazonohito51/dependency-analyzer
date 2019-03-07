<?php
return [
    [
        '@controller' => [
            'define' => ['App'],
        ],
        '@application' => [
            'define' => ['Acme\Application'],
            'white' => ['@controller'],
        ],
        '@domain' => [
            'define' => ['Acme\Domain'],
            'white' => ['@application']
        ]
    ],
    [
        '@entities' => [
            'define' => ['Acme\Domain\Entities'],
            'white' => ['@repositories']
        ],
        '@repositories' => [
            'define' => ['Acme\Domain\Repositories'],
        ]
    ],
];

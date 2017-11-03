<?php

return [
    'app' => [
        'request' => [
            'route' => 'jaxon',
        ],
        'classes' => [
            [
                'directory' => realpath(__DIR__ . '/../src/Ajax/Classes'),
                'namespace' => '\\Lagdo\\Polr\\Admin\\App',
                // 'separator' => '', // '.' or '_'
                // 'protected' => [],
            ],
        ],
        'options' => [
            'classes' => [
                \Lagdo\Polr\Admin\App\Stats::class => [
                    '*' => [
                        'callback' => 'polr.stats.requestCallbacks',
                    ]
                ],
                \Lagdo\Polr\Admin\App\Link::class => [
                    'getAdminLinks' => [
                        'datatables' => 'polr.home.jaxon',
                    ],
                    'getUserLinks' => [
                        'datatables' => 'polr.home.jaxon',
                    ]
                ],
                \Lagdo\Polr\Admin\App\User::class => [
                    'getUsers' => [
                        'datatables' => 'polr.home.jaxon',
                    ]
                ],
            ],
        ],
    ],
    'lib' => [
        // No config options for the Jaxon library
    ],
];

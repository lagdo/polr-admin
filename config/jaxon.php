<?php

return [
    'app' => [
        'request' => [
            'route' => 'jaxon',
        ],
        'classes' => [
            [
                'directory' => app_path('Ajax/Classes'),
                'namespace' => '\\Jaxon\\App',
                // 'separator' => '', // '.' or '_'
                // 'protected' => [],
            ],
        ],
        'options' => [
            'classes' => [
                \Jaxon\App\Stats::class => [
                    '*' => [
                        'callback' => 'polr.stats.requestCallbacks',
                    ]
                ],
                \Jaxon\App\Paginator::class => [
                    '*' => [
                        'dt' => 'polr.home.jaxon',
                    ]
                ]
            ],
        ],
    ],
    'lib' => [
        'core' => [
            'language' => 'en',
            'encoding' => 'UTF-8',
            'request' => [
                // 'uri' => url('jaxon'),
                'csrf_meta' => 'csrf-token',
            ],
            'prefix' => [
                'class' => '',
            ],
            'debug' => [
                'on' => false,
                'verbose' => false,
            ],
            'error' => [
                'handle' => false,
            ],
        ],
        'js' => [
            'lib' => [
                // 'uri' => 'https://cdn.jaxon-php.org/libs/jaxon/1.2.0',
            ],
            'app' => [
                // 'uri' => '',
                // 'dir' => '',
                'extern' => false,
                'minify' => false,
            ],
        ],
        'assets' => [
            'include' => [
                'all' => true,
            ],
        ],
        'dialogs' => [
            'default' => [
                'modal' => 'bootbox',
                'alert' => 'noty',
                'confirm' => 'noty',
            ],
        ],
    ],
];

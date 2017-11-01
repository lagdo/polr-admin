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
                    'adminLinks' => [
                        'datatables' => 'polr.home.jaxon',
                    ],
                    'userLinks' => [
                        'datatables' => 'polr.home.jaxon',
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
                // 'uri' => '',
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

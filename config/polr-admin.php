<?php

return [
    'views' => [
        'polr_admin' => [
            'directory' => realpath(__DIR__ . '/../resources/views'),
            'extension' => '.blade.php',
            'renderer' => 'blade',
            'register' => true,
        ],
    ],
    'directories' => [
        realpath(__DIR__ . '/../ajax/App') => [
            'namespace' => '\\Lagdo\\PolrAdmin\\Ajax\\App',
            'autoload' => false,
            Lagdo\PolrAdmin\Ajax\App\Stats::class => [
                '*' => [
                    'callback' => 'polr.stats.requestCallbacks',
                ]
            ],
            Lagdo\PolrAdmin\Ajax\App\Link::class => [
                'getAdminLinks' => [
                    'datatables' => 'polr.home.jaxon',
                ],
                'getUserLinks' => [
                    'datatables' => 'polr.home.jaxon',
                ]
            ],
            Lagdo\PolrAdmin\Ajax\App\User::class => [
                'getUsers' => [
                    'datatables' => 'polr.home.jaxon',
                ]
            ],
        ],
    ],
    'container' => [
        Lagdo\PolrAdmin\Client::class => function($di) {
            $config = $di->get(Lagdo\PolrAdmin\Package::class)->getConfig();
            $validator = $di->get(Lagdo\PolrAdmin\Helpers\Validator::class);
            return new Lagdo\PolrAdmin\Client($config, $validator);
        },
        Lagdo\PolrAdmin\Datatables\Renderer::class => function() {
            return new Lagdo\PolrAdmin\Datatables\Renderer();
        },
        Lagdo\PolrAdmin\Helpers\Validator::class => function() {
            return new Lagdo\PolrAdmin\Helpers\Validator();
        },
    ],
];

<?php

return array(
    'app' => array(
        'request' => array(
            'route' => 'jaxon',
        ),
        'classes' => array(
            array(
                'directory' => app_path('Ajax/Classes'),
                'namespace' => '\\Jaxon\\App',
                // 'separator' => '', // '.' or '_'
                // 'protected' => array(),
            ),
        ),
    ),
    'lib' => array(
        'core' => array(
            'language' => 'en',
            'encoding' => 'UTF-8',
            'request' => array(
                // 'uri' => url('jaxon'),
                'csrf_meta' => 'csrf-token',
            ),
            'prefix' => array(
                'class' => '',
            ),
            'debug' => array(
                'on' => false,
                'verbose' => false,
            ),
            'error' => array(
                'handle' => false,
            ),
        ),
        'js' => array(
            'lib' => array(
                'uri' => 'https://cdn.jaxon-php.org/libs/jaxon/1.2.0',
            ),
            'app' => array(
                // 'uri' => '',
                // 'dir' => '',
                'extern' => false,
                'minify' => false,
            ),
        ),
        'assets' => array(
            'include' => array(
                'all' => true,
            ),
        ),
        'dialogs' => array(
            'default' => array(
                'modal' => 'bootbox',
                'alert' => 'noty',
                'confirm' => 'noty',
            ),
        ),
    ),
);

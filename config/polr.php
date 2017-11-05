<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Polr Instance
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the Polr endpoints below you wish
    | to use as your default instance.
    |
    */

    'default' => 'first',

    /*
    |--------------------------------------------------------------------------
    | Polr Instances
    |--------------------------------------------------------------------------
    |
    | Here are each of the Polr Instances to be controlled from this dashboard.
    |
    */

    'endpoints' => [
        'first' => [
            'name'       => 'First Instance', // The name of this instance for dropdown menu
            'url'        => 'http://polr.domain.com',
            'api'        => 'api/v2',
            'key'        => 'PolrApiKey', // The user API key on the Polr instance
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Polr Templates
    |--------------------------------------------------------------------------
    |
    | These templates will be used to print Polr HTML, Javascript and CSS codes.
    |
    */

    'templates' => [
        // 'html' => '',    // HTML
        // 'css' => '',     // CSS includes
        // 'js' => '',      // Javascript includes
    ],
];

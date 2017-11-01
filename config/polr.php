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
            'url'        => 'http://polr.domain.com/api/v2',
            'key'        => 'PolrApiKey', // The user API key on the Polr instance
            'name'       => 'First Instance', // The name of this instance for dropdown menu
        ],
    ],
];

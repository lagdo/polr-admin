Polr Admin
==========

An alternative admin dashboard for the Polr URL shortener.

Our goal is to provide a dashboard with advanced features for managing multiple Polr instances.

This branch is packaged as a Laravel extension.

Features
--------

The features are mostly the same as in the Polr Admin section, but with few differences.

- The dashboard is based on Laravel instead of Lumen.
- AngularJS is dropped in favor of Jaxon [https://www.jaxon-php.org](https://www.jaxon-php.org).
- A `Confirm Password` field is added to the `Change Password` form.
- The `Settings` tab allows to choose a Polr instance from a dropdown list.
- The dashboard can display stats for all links.
- The URL shortening and link stats features are fully implemented with Ajax, using Jaxon.
- The link redirection feature is not included.

Installation
------------

Add the Github repository and package in the `composer.json` file.

```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/lagdo/polr-admin"
        }
    ],
    "require": {
        "lagdo/polr-admin": "dev-extension"
    }
}
```

Add `Jaxon`, `Datatables` and `Polr Admin` to the `providers` and `aliases` entries in `app.php`.

```php
    'providers' => [
        // Jaxon Ajax library
        Jaxon\Laravel\JaxonServiceProvider::class,
        // Datatables
        Yajra\Datatables\DatatablesServiceProvider::class,
        // Polr Admin
        Lagdo\Polr\Admin\PolrAdminServiceProvider::class,
    ],

    'aliases' => [
        // Datatables
        'Datatables'   => Yajra\Datatables\Facades\Datatables::class,
    ],
```

Publish the public files.

```bash
php artisan vendor:publish --tag=public --force
```

Publish the config files.
This will copy the `jaxon.php` and `polr.php` files in the `config` dir.

```bash
php artisan vendor:publish --provider="Lagdo\Polr\Admin\PolrAdminServiceProvider" --tag="config"
```

Configuration
-------------

Edit `config/polr.php` config file, and list your Polr intances.

```php
<?php

return [
    'default' => 'first',
    'endpoints' => [
        'first' => [
            'url'        => 'http://polr.domain.com/api/v2',
            'key'        => 'PolrApiKey', // The user API key on the Polr instance
            'name'       => 'First Instance', // The name of this instance for dropdown menu
        ],
    ],
];
```

Usage
-----

Coming soon.

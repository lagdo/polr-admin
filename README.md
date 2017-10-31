Polr Admin
==========

An alternative admin dashboard for the Polr URL shortener.

Our goal is to provide a dashboard with advanced features for managing multiple Polr instances.

Features
--------

The features are mostly the same as in the Polr Admin section, but with few differences.

- The dashboard is based on Laravel instead of Lumen.
- AngularJS is dropped in favor of Jaxon [https://www.jaxon-php.org](https://www.jaxon-php.org).
- A `Confirm Password` field is added to the `Change Password` form.
- The `Settings` tab allows to choose a Polr instance.
- The dashboard can display stats for all links.
- The URL shortening and link stats features are fully implemented with Ajax, using Jaxon.
- The link redirection feature is not included.

Installation
------------

Clone this repository to a local directory.

Get into the installed directory and run `composer install` to install the dependencies.

Fill the `env.example` file with the same parameters as your Polr installation, and rename to `.env`.

Setup your web server to serve the application from the `public` directory.
See the `Running Polr on...` section in the [Polr installation guide](https://docs.polrproject.org/en/latest/user-guide/installation/) to learn how to configure your prefered web server.

Configuration
-------------

Create a `polr.php` config file, where your Polr intances are defined.

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
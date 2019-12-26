Polr Admin
==========

An alternative admin dashboard for the Polr URL shortener.

The goal is to provide a dashboard for managing one or more Polr instances.

This dashboard is packaged as a [Jaxon](https://www.jaxon-php.org) package, so it can be installed on any PHP web application.

The [Polr API](https://github.com/lagdo/polr-api) package needs to be installed on each instance of Polr to be managed.

Features
--------

The features are mostly the same as Polr, but with few differences.

- The Polr instance to manage is chosen from a dropdown list.
- The dashboard can display stats for all links.
- The user related features are not included.
- AngularJS is dropped in favor of Jaxon [https://www.jaxon-php.org](https://www.jaxon-php.org).
- The package is a [Jaxon](https://www.jaxon-php.org) package, and not a standalone application.
- All features are fully implemented with Ajax, using Jaxon.

Documentation
-------------

0. Install the jaxon library so it bootstraps from a config file and handles ajax requests. Here's the [documentation](https://www.jaxon-php.org/docs/v3x/advanced/bootstrap.html).

1. Install this package with Composer. If a [Jaxon plugin](https://www.jaxon-php.org/docs/v3x/plugins/frameworks.html) exists for your framework, you can also install it. It will automate the previous step.

2. Declare the package and the Polr servers in the `app` section of the [Jaxon configuration file](https://www.jaxon-php.org/docs/v3x/advanced/bootstrap.html).

```php
    'app' => [
        // Other config options
        // ...
        'packages' => [
            Lagdo\PolrAdmin\Package::class => [
                'servers' => [
                    'first' => [
                        'name'       => 'First server',
                        'url'        => 'https://first.server.addr',
                        'api'        => 'api/v2',
                        'key'        => 'first.server.key',
                    ],
                    'second' => [
                        'name'       => 'Second server',
                        'url'        => 'https://second.server.addr',
                        'api'        => 'api/v2',
                        'key'        => 'second.server.key',
                    ],
                ],
            ],
        ],
    ],
```

3. Insert the CSS and javascript codes in the HTML pages of your application using calls to `jaxon()->getCss()` and `jaxon()->getScript(true)`.

4. In the page that displays the dashboard, insert its HTML code with a call to `jaxon()->package(\Lagdo\PolrAdmin\Package::class)->getHtml()`. Two cases are then possible.

    - If the dashboard is displayed on a dedicated page, make a call to `jaxon()->package(\Lagdo\PolrAdmin\Package::class)->ready()` when loading the page.

    - If the dashboard is loaded with an Ajax request in a page already displayed, execute the javascript code returned the call to `jaxon()->package(\Lagdo\PolrAdmin\Package::class)->getReadyScript()` when loading the page.


Notes
-----

This package uses the Blade template engine to display its views.
As a consequence, when using a PHP framework other than Laravel, the Blade package for Jaxon must also be installed.

```json
{
    "require": {
        "jaxon-php/jaxon-blade": "^3.0",
    }
}
```

Contribute
----------

- Issue Tracker: github.com/lagdo/polr-admin/issues
- Source Code: github.com/lagdo/polr-admin

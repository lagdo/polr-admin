Polr Admin
==========

An alternative admin dashboard for the Polr URL shortener.

The goal is to provide a dashboard for managing multiple Polr instances.

This dashboard is packaged as a [Jaxon](https://www.jaxon-php.org) extension, so it can be installed on any running PHP web application.

The [Polr API](https://github.com/lagdo/polr-api) package needs to be installed on each instance of Polr to be managed.

Features
--------

The features are mostly the same as Polr, but with few differences.

- The `Settings` tab allows to choose a Polr instance from a dropdown list.
- The dashboard can display stats for all links.
- The user creation and deletion, link redirection and password change features are not included.
- AngularJS is dropped in favor of Jaxon [https://www.jaxon-php.org](https://www.jaxon-php.org).
- The package is a [Jaxon](https://www.jaxon-php.org) extension, and not a standalone application.
- All features are fully implemented with Ajax, using Jaxon.

Jaxon package
-------------

Depending on the PHP framework used by the application, a different Jaxon package must be installed and configured together with Polr Admin.

Jaxon packages exist for the following frameworks:

- Laravel [https://github.com/jaxon-php/jaxon-laravel](https://github.com/jaxon-php/jaxon-laravel)
- Symfony [https://github.com/jaxon-php/jaxon-symfony](https://github.com/jaxon-php/jaxon-symfony)
- CodeIgniter [https://github.com/jaxon-php/jaxon-codeigniter](https://github.com/jaxon-php/jaxon-codeigniter)
- CakePHP [https://github.com/jaxon-php/jaxon-cake](https://github.com/jaxon-php/jaxon-cake)
- Yii Framework [https://github.com/jaxon-php/jaxon-yii](https://github.com/jaxon-php/jaxon-yii)
- Zend Framework [https://github.com/jaxon-php/jaxon-zend](https://github.com/jaxon-php/jaxon-zend)

In the case an unsupported framework (or no framework) is used, the [Armada package](https://github.com/jaxon-php/jaxon-armada) must be installed instead.

The Jaxon packages are [documented online](https://www.jaxon-php.org/docs/plugins/integration.html).

Documentation
-------------

- [Using with Laravel](docs/laravel.md)

Notice for other frameworks
===========================

This package uses the Blade template engine to display its views.
As a consequence, when using a framework other than Laravel, the Blade package for Jaxon must also be installed.

```json
{
    "require": {
        "jaxon-php/jaxon-codeigniter": "~2.0",
        "jaxon-php/jaxon-blade": "~2.0",
        "lagdo/polr-admin": "~0.1"
    }
}
```

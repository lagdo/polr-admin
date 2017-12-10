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

The following example uses to the Laravel framework, but the same principles apply to other frameworks.

Installation
------------

Add the package in the `composer.json` file, and run `composer update`.

```json
{
    "require": {
        "jaxon-php/jaxon-laravel": "~2.0",
        "lagdo/polr-admin": "dev-master"
    }
}
```

This package uses the Blade template engine to display its views.
As a consequence, when using a framework other than Laravel, the Blade package for Jaxon must also be installed.

```json
{
    "require": {
        "jaxon-php/jaxon-codeigniter": "~2.0",
        "jaxon-php/jaxon-blade": "~2.0",
        "lagdo/polr-admin": "dev-master"
    }
}
```

Add `Jaxon` to the `providers` entries in `app.php`.

```php
    'providers' => [
        // ...
        // Jaxon Ajax library
        Jaxon\Laravel\JaxonServiceProvider::class,
    ],
```

Publish the public files.

```bash
php artisan vendor:publish --tag=public --force
```

Publish the config files.

```bash
php artisan vendor:publish --provider="Jaxon\Laravel\JaxonServiceProvider" --tag="config"
```

This will create the `jaxon.php` files in the `config` dir.

Configuration
-------------

Manually create and edit the `config/polradmin.php` config file, to list the Polr intances to be managed.

```php
return [
    'default' => 'first',
    'endpoints' => [
        'first' => [
            'name'       => 'First Instance', // The name of this instance for dropdown menu
            'url'        => 'http://polr.domain.com',
            'api'        => 'api/v2',
            'key'        => 'PolrApiKey', // The user API key on the Polr instance
        ],
    ],
];
```

Page template
-------------

The package provides various functions returning the HTML, Javascript and CSS codes to be inserted into a template.

Let's consider the following Laravel controller, where the `Jaxon` instance is injected in the `index()` method and passed to a template.

```php
use Jaxon\Laravel\Jaxon;

class PolrController extends Controller
{
    public function index(Jaxon $jaxon)
    {
        // Get the Polr Admin instance
        $polr = $jaxon->package('polr.admin');
        // Load the Polr Admin configuration
        $polr->config(config_path('polradmin.php'));

        // Register Jaxon classes
        $jaxon->register();

        return view('index', ['jaxon' => $jaxon, 'polr' => $polr]);
    }
}
```

The following calls will return the codes to be inserted in the template.

- `$jaxon->css()`: The Jaxon CSS includes.
- `$jaxon->js()`: The Jaxon Javascript includes.
- `$jaxon->script()`: The Jaxon Javascript code.
- `$polr->css()`: The Polr Admin CSS includes.
- `$polr->js()`: The Polr Admin Javascript includes.
- `$polr->html()`: The Polr Admin HTML code.
- `$polr->ready()`: The Javascript code to run on page ready.

So a sample template will look like this.

```html
@extends('layouts.base')

@section('css')
{!! $jaxon->css() !!}

{!! $polr->css() !!}
@endsection

@section('content')

{!! $polr->html() !!}

@endsection

@section('js')
{!! $jaxon->js() !!}
{!! $jaxon->script() !!}

{!! $polr->js() !!}

<script type="text/javascript">
$(document).ready(function() {
    {!! $polr->ready() !!}
});
</script>
@endsection
```

Notice for other frameworks
===========================

When using a framework other than Laravel, the Jaxon object is an instance of a different class.
The config file path (maybe its format too), the controller and the template engine are also different.

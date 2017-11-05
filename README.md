Polr Admin
==========

An alternative admin dashboard for the Polr URL shortener.

Our goal is to provide a dashboard with advanced features for managing multiple Polr instances.

This dashboard is packaged as a Laravel extension.
Firstly, it makes the package simpler since there is no need to deal with user management features.
Then, it lets the end user choose how to integrate: in an existing Laravel application, or in a third-party Laravel admin panel.

The https://github.com/lagdo/polr-api package needs to be installed on each instance of Polr to be managed.

Features
--------

The features are mostly the same as Polr, but with few differences.

- The package is a Laravel extension, and not a standalone application.
- AngularJS is dropped in favor of Jaxon [https://www.jaxon-php.org](https://www.jaxon-php.org).
- The `Settings` tab allows to choose a Polr instance from a dropdown list.
- The dashboard can display stats for all links.
- All features are fully implemented with Ajax, using Jaxon.
- The user creation and deletion, link redirection and password change features are not included.

Installation
------------

Add the Github repository and package in the `composer.json` file, and run `composer update`.

```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/lagdo/polr-admin"
        }
    ],
    "require": {
        "lagdo/polr-admin": "dev-master"
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

```bash
php artisan vendor:publish --provider="Jaxon\Laravel\JaxonServiceProvider" --tag="config"
php artisan vendor:publish --provider="Lagdo\Polr\Admin\PolrAdminServiceProvider" --tag="config"
```

This will create the `jaxon.php` and `polradmin.php` files in the `config` dir.

Configuration
-------------

Edit the `config/polradmin.php` config file, and list the Polr intances.

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

Integration
-----------

The package provides various functions returning the HTML, Javascript and CSS codes to be inserted into a template.

First of all, let's consider the following Laravel controller,
where the `Jaxon` and `PolrAdmin` objects are injected in the `index()` method and passed to a template.

```php
use Jaxon\Laravel\Jaxon;
use Lagdo\Polr\Admin\PolrAdmin;

class PolrController extends Controller
{
    public function index(Jaxon $jaxon, PolrAdmin $polr)
    {
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
Installation
------------

Add the package in the `composer.json` file, and run `composer update`.

```json
{
    "require": {
        "jaxon-php/jaxon-laravel": "~2.0",
        "lagdo/polr-admin": "~0.1"
    }
}
```

Polr configuration
------------------

Manually create and edit the `config/polradmin.php` config file, and list the Polr intances to be managed.

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

Jaxon configuration
-------------------

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
Edit the file and add the following options.

```php
return [
    'app' => [
    ],
    'lib' => [
        'core' => [
            'request' => [
                'uri' => 'jaxon',
            ],
            'prefix' => [
                'class' => '',
            ],
        ],
        'js' => [
            'lib' => [
                // 'uri' => '',
            ],
            'app' => [
                // 'uri' => '',
                // 'dir' => '',
                'extern' => false,
                'minify' => false,
            ],
        ],
        'dialogs' => [
            'default' => [
                'modal' => 'bootstrap',
                'alert' => 'noty',
                'confirm' => 'noty',
            ],
        ],
    ],
];
```

The Controller
--------------

Add the following controller, where the `Jaxon` and `Polr` instances are initialized and passed to a template.

```php
namespace App\Http\Controllers;

use Jaxon\Laravel\Jaxon;

class PolrController extends Controller
{
    public function __construct(Jaxon $jaxon)
    {
        // Set the Jaxon instance
        $this->jaxon = $jaxon;
        // Set the Polr Admin instance
        $this->polr = $this->jaxon->package('polr.admin');
        // Load the Polr Admin configuration
        $this->polr->config(config_path('polradmin.php'));
    }

    // Show the Polr Admin page
    public function index()
    {
        // Register Jaxon classes
        $this->jaxon->register();

        return view('index', ['jaxon' => $this->jaxon, 'polr' => $this->polr]);
    }

    // Process Jaxon Ajax requests
    public function jaxon()
    {
        // Process the Jaxon request
        if($this->jaxon->canProcessRequest())
        {
            $this->jaxon->processRequest();
            return $this->jaxon->httpResponse();
        }
    }
}
```

The routing
-----------

In the `PolrController` controller, the `index()` method shows the `Polr Admin` page, while the `jaxon()` method processes Ajax requests.
Two routes need to be defined to these methods.

The path to the `jaxon()` method shall be the same as the value of the `lib.core.request.uri` in the `config/jaxon.php` config file.

The page template
-----------------

The package provides various functions returning the HTML, Javascript and CSS codes to be inserted into a template.

- `$jaxon->css()`: The Jaxon CSS includes.
- `$jaxon->js()`: The Jaxon Javascript includes.
- `$jaxon->script()`: The Jaxon Javascript code.
- `$polr->css()`: The Polr Admin CSS includes.
- `$polr->js()`: The Polr Admin Javascript includes.
- `$polr->html()`: The Polr Admin HTML code.
- `$polr->ready()`: The Javascript code to run when the page has loaded.

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

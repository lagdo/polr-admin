<?php

namespace Lagdo\Polr\Admin;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\ServiceProvider;

use GuzzleHttp\Client as RestClient;

use Datatables;
use Lagdo\Polr\Admin\Ext\Datatables\Plugin;
use Lagdo\Polr\Admin\Ext\Datatables\Renderer;

class PolrAdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set views directory
        view()->addNamespace('polr_admin', __DIR__ . '/../resources/views');

        // Publish package assets
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('lagdo/polr/admin'),
        ], 'public');

        // Publish package config
        $this->publishes([
            __DIR__ . '/../config/polr.php' => config_path('polradmin.php'),
        ], 'config');

	    // Register an instance of the Datatables plugin
	    jaxon_register_plugin(new Plugin());

        // Read config file
        $jaxon = jaxon();
        $sentry = $jaxon->sentry();
        $sConfigFile = __DIR__ . '/../config/jaxon.php';
        $xAppConfig = $jaxon->readConfigFile($sConfigFile, 'lib', 'app');
        $sentry->addClassOptions($xAppConfig);
        $sentry->addClassNamespaces($xAppConfig);

        // Set the class initializer
        $this->apiKey = null;
        $this->apiClient = null;
        $sentry = jaxon()->sentry();
        $sentry->addClassInitializer('Lagdo\Polr\Admin\App',
            function($instance) use ($sentry){
                // Polr plugin instance
                $instance->polr = app()->make('lagdo.polr.admin');

                // Polr API Client
                if($this->apiClient == null)
                {
                    $cfgKey = 'polradmin.endpoints.' . session()->get('polr.endpoint');
                    $this->apiKey = config($cfgKey . '.key');
                    $uri = rtrim(config($cfgKey . '.url'), '/') . '/' .
                        trim(config($cfgKey . '.api'), '/') . '/';
                    $this->apiClient = new RestClient(['base_uri' => $uri]);
                }
                // Save the HTTP REST client
                $instance->apiKey = $this->apiKey;
                $instance->apiClient = $this->apiClient;

                // Dialogs and notifications are implemented by the Dialogs plugin
                $response = $sentry->ajaxResponse();
                $instance->dialog = $response->dialog;
                $instance->notify = $response->dialog;

                // The HTTP Request
                $instance->httpRequest = app()->make('request');
                
                // Save the Datatables renderer and request in the class instance
                $instance->dtRequest = Datatables::getRequest();
                $instance->dtRenderer = app()->make('jaxon.dt.renderer');
            }
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // The Datatables row renderer
	    $this->app->singleton('jaxon.dt.renderer', Renderer::class);

	    // Register the Polr Admin singleton
        $this->app->singleton('lagdo.polr.admin', function ($app)
        {
            return new PolrAdmin($app->make('jaxon'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'lagdo.polr.admin'
        );
    }
}

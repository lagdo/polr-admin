<?php

namespace Lagdo\Polr\Admin;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\ServiceProvider;

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
        // Load package routes
        if(!$this->app->routesAreCached())
        {
            require(__DIR__ . '/Http/routes.php');
        }

        // Set views directory
        view()->addNamespace('polr_admin', __DIR__ . '/../resources/views');

        // Publish package assets
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('lagdo/polr/admin'),
        ], 'public');

        // Publish package config
        $this->publishes([
            __DIR__ . '/../config/jaxon.php' => config_path('jaxon.php'),
            __DIR__ . '/../config/polr.php' => config_path('polr.php'),
        ], 'config');
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

	    // Register an instance of the Datatables plugin
	    jaxon_register_plugin(new Plugin());

        // Register the Polr Admin singleton
        $this->app->singleton('lagdo.polr.admin', function ($app)
        {
            return new PolrAdmin();
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

<?php

namespace Lagdo\Polr\Admin;

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
        $sentry->addViewNamespaces($xAppConfig);

        // Set the class initializer
        $sentry->addClassInitializer('Lagdo\Polr\Admin\App',
            function($instance){
                $polr = app()->make('lagdo.polr.admin');
                // Init the Jaxon class instance
                $polr->initInstance($instance);
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
            'jaxon.dt.renderer',
            'lagdo.polr.admin',
        );
    }
}

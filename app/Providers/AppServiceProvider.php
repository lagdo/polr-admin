<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // The Datatables row renderer
	    $this->app->singleton('jaxon.dt.renderer', \Jaxon\Ext\Datatables\Renderer::class);
	    // Register an instance of the Datatables plugin
	    jaxon_register_plugin(new \Jaxon\Ext\Datatables\Plugin());
    }
}

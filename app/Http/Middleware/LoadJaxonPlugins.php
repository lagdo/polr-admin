<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class LoadJaxonPlugins
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
	    // Register an instance of the Datatables plugin
	    jaxon_register_plugin(new \Jaxon\Ext\Datatables\Plugin());
		return $next($request);
	}
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class SetEndpoint
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($this->auth->check())
        {
            // Get Polr endpoints from config
            if(!session()->has('polr.endpoint'))
            {
                $current = config('polr.default', '');
                session()->set('polr.endpoint', $current);
            }
            else
            {
                $current = session()->get('polr.endpoint');
            }
            $endpoints = [
                'current' => ['id' => $current, 'name' => config('polr.endpoints.' . $current . '.name')],
                'names' => [],
            ];
            foreach(config('polr.endpoints') as $id => $endpoint)
            {
                $endpoints['names'][$id] = $endpoint['name'];
            }
            view()->share('endpoints', $endpoints);
        }
        else
        {
            view()->share('endpoints', null);
        }

        return $next($request);
    }
}

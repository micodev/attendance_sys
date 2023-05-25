<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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
        $response = $next($request);

        $response->header("Access-Control-Allow-Origin", "*");
        $response->header("Access-Control-Allow-Credentials", "true");
        $response->header("Access-Control-Max-Age", "600");    // cache for 10 minutes

        $response->header("Access-Control-Allow-Methods", "POST, GET, OPTIONS, DELETE, PUT"); //Make sure you remove those you do not want to support

        $response->header("Access-Control-Allow-Headers", "Content-Type, Accept, Authorization, X-Requested-With, Application");

        return $response;
    }

}

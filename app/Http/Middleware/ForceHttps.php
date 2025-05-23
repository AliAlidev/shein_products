<?php

namespace App\Http\Middleware;

use Closure;

class ForceHttps
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
//        dd(env('APP_URL'));
        if (in_array(env('APP_ENV'), ['production'])) {
            return redirect()->secure($request->getRequestUri());
        }
//
//        return $next($request);
    }
}

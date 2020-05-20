<?php

namespace App\Http\Middleware;

use Closure;

class PinCodeForBetaMiddleware
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
        if (! config('app.beta')) {
            return $next($request);
        }

        if ($request->pin) {
            session()->put('pin', $request->pin);
        }

        if (config('app.beta') && session()->get('pin') === '07775000') {
            return $next($request);
        }

        return response(view('pin'));
    }
}

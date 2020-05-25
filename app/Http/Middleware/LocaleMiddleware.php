<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Http\Request;

class LocaleMiddleware
{
    protected $languages = ['en', 'ar'];

    public function handle(Request $request, Closure $next)
    {
        if (! Session::has('locale')) {
            Session::put('locale', $request->getPreferredLanguage($this->languages));
        }

        app()->setLocale(Session::get('locale'));

        return $next($request);
    }
}

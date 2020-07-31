<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocaleMiddleware
{
    protected $languages = ['en', 'ar'];

    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('locale')) {
            session()->put('locale', $request->getPreferredLanguage($this->languages));
        }

        app()->setLocale(session()->get('locale'));

        return $next($request);
    }
}

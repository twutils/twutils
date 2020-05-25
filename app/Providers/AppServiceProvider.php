<?php

namespace App\Providers;

use Schema;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use MimeTyper\Repository\MimeDbRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Collection::macro('file', function () {
            \File::put(base_path('temp/collect-'.time().'-'.now()->format('Y-m-d_H-m-s').'.json'), json_encode($this->items, JSON_PRETTY_PRINT));

            return $this;
        });

        if (! env('ENABLE_DEBUGBAR')) {
            \Debugbar::disable();
        }

        try {
            if (Str::contains($_SERVER['HTTP_REFERER'], '3000')) {
                config()->set('services.twitter.redirect', sprintf(env('TWITTER_REDIRECT'), '3000'));
            }
        } catch (\Exception $e) {
        }
        app()->bind(\App\TwUtils\ITwitterConnector::class, \App\TwUtils\TwitterConnector::class);
        app()->bind('MimeDB', MimeDbRepository::class);
        app()->bind('HttpClient', Client::class);

        View::composer('layout._navbar', function ($view) {
            Config::set('app.version', Cache::get('app.version', ''));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}

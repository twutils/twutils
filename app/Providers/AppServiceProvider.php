<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(255);

        Collection::macro('file', function () {
            \File::put(base_path('temp/collect-'.time().'-'.now()->format('Y-m-d_H-m-s').'.json'), json_encode($this->items, JSON_PRETTY_PRINT));

            return $this;
        });

        /**
         * Similar to pluck, with the exception that it can 'pluck' more than one column.
         * This method can be used on either Eloquent models or arrays.
         *
         * credits: https://stackoverflow.com/a/54236337/4330182
         *
         * @param string|array $cols Set the columns to be selected.
         * @return Collection A new collection consisting of only the specified columns.
         */
        Collection::macro('pick', function ($cols = ['*']) {
            $cols = is_array($cols) ? $cols : func_get_args();
            $obj = clone $this;

            // Just return the entire collection if the asterisk is found.
            if (in_array('*', $cols)) {
                return $this;
            }

            return $obj->transform(function ($value) use ($cols) {
                $ret = [];
                foreach ($cols as $col) {
                    // This will enable us to treat the column as a if it is a
                    // database query in order to rename our column.
                    $name = $col;
                    if (preg_match('/(.*) as (.*)/i', $col, $matches)) {
                        $col = $matches[1];
                        $name = $matches[2];
                    }

                    // If we use the asterisk then it will assign that as a key,
                    // but that is almost certainly **not** what the user
                    // intends to do.
                    $name = str_replace('.*.', '.', $name);

                    // We do it this way so that we can utilise the dot notation
                    // to set and get the data.
                    Arr::set($ret, $name, data_get($value, $col));
                }

                return $ret;
            });
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

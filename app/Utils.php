<?php

namespace App;

class Utils
{
    public static function setup_twitter_config_for_read_write(): void
    {
        if (app()->isProduction()) {
            config()->set('services.twitter.redirect', env('TWITTER_REDIRECT_READ_WRITE'));
        } else {
            config()->set('services.twitter.redirect', sprintf(env('TWITTER_REDIRECT_READ_WRITE'), env('APP_PORT')));
        }

        config()->set('services.twitter.client_id', env('TWITTER_READ_WRITE_CLIENT_ID'));
        config()->set('services.twitter.client_secret', env('TWITTER_READ_WRITE_CLIENT_SECRET'));
    }

    // https://gist.github.com/liunian/9338301#gistcomment-1970661
    public static function humanize_bytes(int $bytes) : string
    {
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), [0, 0, 2, 2, 3][$i]).' '.['B', 'KB', 'MB', 'GB', 'TB'][$i];
    }
}

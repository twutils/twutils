<?php

namespace App;

class Utils
{
    // https://gist.github.com/liunian/9338301#gistcomment-1970661
    public static function humanize_bytes(int $bytes) : string
    {
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), [0, 0, 2, 2, 3][$i]).' '.['B', 'KB', 'MB', 'GB', 'TB'][$i];
    }
}

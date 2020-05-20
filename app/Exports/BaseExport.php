<?php

namespace App\Exports;

use Illuminate\Support\Str;

class BaseExport
{
    public function formatText($text)
    {
        if (Str::startsWith($text, '=')) {
            $text = Str::start($text, '\'');
        }

        return $text;
    }
}

<?php

namespace Tests;

class ImageMock
{
    public static $lastData = null;

    public function save($path)
    {
        return 'image';
    }

    public function __call($method, $parameters)
    {
        static::$lastData = compact('method', 'parameters');

        return $this;
    }
}

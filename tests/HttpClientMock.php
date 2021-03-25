<?php

namespace Tests;

class HttpClientMock
{
    public static $lastData = null;

    public static $lastUrlCalled = null;

    public static $throwException = 0;

    public function get($url, ...$args)
    {
        static::$lastUrlCalled = $url;

        if (static::$throwException !== 0) {
            static::$throwException--;
            throw new \Exception('Error Processing Request', 1);
        }

        return $this;
    }

    public function getHeaderLine($header, ...$args)
    {
        if ($header == 'Content-Type') {
            return app('MimeDB')->findType(pathinfo(static::$lastUrlCalled)['extension']);
        }

        return $this;
    }

    public function getContents()
    {
        $extension = pathinfo(static::$lastUrlCalled)['extension'];

        if (in_array($extension, ['png', 'jpg', 'jpeg'])) {
            return file_get_contents(base_path('tests/_stubs/photo').'.'.$extension);
        }

        if ($extension == 'mp4') {
            return file_get_contents(base_path('tests/_stubs/video.mp4'));
        }

        throw new \Exception("Stub for the extension [$extension] not found");
    }

    public function throwException($times)
    {
        static::$throwException = $times;
    }

    public function __call($method, $parameters)
    {
        static::$lastData = compact('method', 'parameters');

        return $this;
    }

    public function __toString()
    {
        return '';
    }
}

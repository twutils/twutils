<?php

namespace App\TwUtils\State;

class DownloadStatus
{
    protected $ok;
    protected $path;

    function __construct($ok, $path)
    {
        $this->ok = $ok;
        $this->path = $path;
    }

    public function isOk()
    {
        return $this->ok;
    }

    public function getPath()
    {
        return $this->path;
    }
}
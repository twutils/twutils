<?php

namespace App\TwUtils\State;

use Illuminate\Contracts\Support\Arrayable;

class DownloadStatus implements Arrayable
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

    public function toArray()
    {
        return ['ok' => $this->ok, 'path' => $this->path];
    }
}
<?php

namespace App\TwUtils\State;

class Media
{

    protected $data;
    protected $type;

    public function __construct($data, $type = '')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPath()
    {
        return $this->data;
    }
}
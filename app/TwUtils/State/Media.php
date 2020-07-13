<?php

namespace App\TwUtils\State;

class Media
{

    public $data;
    public $type;

    public function __construct($data, $type = '')
    {
        $this->data = $data;
        $this->type = $type;
    }
}
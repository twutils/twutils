<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tweep extends Model
{
    protected $guarded = ['id'];

    protected function tweets()
    {
        return $this->hasMany(Tweet::class);
    }
}

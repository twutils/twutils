<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tweep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function tweets()
    {
        return $this->hasMany(Tweet::class, 'tweep_id_str');
    }
}

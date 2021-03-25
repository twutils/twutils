<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    public $timestamps = false;

    protected $casts = ['followed_by' => 'boolean'];

    //

    public function tweep()
    {
        return $this->belongsTo(Tweep::class, 'tweep_id_str', 'id_str');
    }
}

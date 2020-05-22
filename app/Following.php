<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Following extends Model
{
    public $timestamps = false;
    protected $casts = ['followed_by' => 'boolean'];
    //

    public function tweep()
    {
        return $this->belongsTo(Tweep::class, 'tweep_id_str', 'id_str');
    }
}

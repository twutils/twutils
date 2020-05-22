<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialUser extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'scope'            => 'array',
        'followers_count'  => 'integer',
        'friends_count'    => 'integer',
        'favourites_count' => 'integer',
        'statuses_count'   => 'integer',
    ];
    protected $appends = [
        'scopeIsActive',
    ];
    protected $hidden = [
        'token', 'token_secret',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $socialUser) {
            $socialUser->tasks()->each(function (Task $task) {
                $task->delete();
            });
        });
    }

    protected function getScopeStringAttribute()
    {
        return collect($this->scope)
                ->sort()
                ->values()
                ->map(function ($scope) {
                    return __('messages.'.$scope);
                })
                ->implode(', ');
    }

    protected function getScopeIsActiveAttribute()
    {
        return !empty($this->token);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'socialuser_id', 'id');
    }
}

<?php

namespace App;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token', 'api_token',
    ];

    protected $dates = [
        'created_at', 'updated_at',
    ];

    protected $casts = [
        'lastlogin_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $user) {
            $user->socialUsers()->each(function (SocialUser $socialUser) {
                $socialUser->delete();
            });
        });
    }

    public function socialUsers()
    {
        return $this->hasMany(\App\SocialUser::class);
    }

    public static function getColumns()
    {
        $instance = new self();

        return $instance->getConnection()->getSchemaBuilder()->getColumnListing($instance->getTable());
    }

    public function getAvatarAttribute()
    {
        $socialUser = $this->socialUsers[0] ?? null;
        if (! is_null($socialUser)) {
            return $socialUser->avatar;
        }

        return null;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('username')
            ->saveSlugsTo('username')
            ->usingSeparator('_')
            ->slugsShouldBeNoLongerThan(254);
    }
}

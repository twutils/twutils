<?php

namespace App\Models;

use App\Models\SocialUser;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
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
        return $this->hasMany(SocialUser::class)->orderBy('updated_at', 'desc');
    }

    public function tasks()
    {
        return $this->hasManyThrough(Task::class, SocialUser::class, 'id', 'socialuser_id', 'id', 'id');
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

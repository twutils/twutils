<?php

namespace App\Models;

use App\Jobs\BuildTaskView;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Jobs\Actions\TaskCreated;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\CleaningAllTweetsAndTweeps;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\TwUtils\TwitterOperations\DestroyLikesOperation;
use App\TwUtils\TwitterOperations\DestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation;

class Task extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['baseName', 'removedCount', 'componentName'];

    protected $hidden = ['exception'];

    protected $with = ['exports'];

    protected $withCount = ['likes', 'followings', 'followers'];

    protected $casts = [
        'extra'             => 'array',
        'targeted_task_id'  => 'int',
    ];

    public const TWEETS_LISTS_TYPES = [
        FetchLikesOperation::class,
        FetchEntitiesLikesOperation::class,
        FetchUserTweetsOperation::class,
        FetchEntitiesUserTweetsOperation::class,
    ];

    public const TWEETS_LISTS_WITH_ENTITIES_TYPES = [
        FetchEntitiesLikesOperation::class,
        FetchEntitiesUserTweetsOperation::class,
    ];

    public const USERS_LISTS_TYPES = [
        FetchFollowingOperation::class,
        FetchFollowersOperation::class,
    ];

    public const TWEETS_DESTROY_TWEETS_TYPES = [
        DestroyLikesOperation::class,
        DestroyTweetsOperation::class,
    ];

    public const TWEETS_MANAGED_DESTROY_TYPES = [
        ManagedDestroyLikesOperation::class,
        ManagedDestroyTweetsOperation::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(
            // TODO: Dispatch it as a real job
            fn (self $task) => (new TaskCreated($task))->handle()
        );

        static::updated(function (self $task) {
            if (
                $task->status === 'completed' &&
                array_key_exists('status', $task->getDirty())
            ) {
                $task->exports->map(function (Export $export) {
                    $export->status = Export::STATUS_STARTED;
                    $export->save();
                });

                dispatch(new BuildTaskView($task));
            }
        });

        static::deleting(function (self $task) {
            $taskId = $task->id;

            TaskTweet::where('task_id', $taskId)->delete();
            Following::where('task_id', $taskId)->delete();
            Follower::where('task_id', $taskId)->delete();

            $task->managedTasks->map->delete();
            $task->exports->map->delete();
        });

        static::deleted(function (self $task) {
            dispatch(new CleaningAllTweetsAndTweeps);
        });
    }

    public function getTaskTweeps()
    {
        $tweeps = collect([]);

        if (in_array($this->type, self::TWEETS_LISTS_TYPES)) {
            $tweeps = $this->likes
                        ->pluck('tweep')
                        ->unique('id');
        }

        if ($this->type === FetchFollowingOperation::class) {
            $tweeps = $this->followings()
                    ->with('tweep')
                    ->get()
                    ->unique('id')
                    ->pluck('tweep');
        }

        if ($this->type === FetchFollowersOperation::class) {
            $tweeps = $this->followers()
                    ->with('tweep')
                    ->get()
                    ->unique('id')
                    ->pluck('tweep');
        }

        return $tweeps;
    }

    public function likes()
    {
        return $this->tweets();
    }

    public function tweets()
    {
        return $this->belongsToMany(Tweet::class, 'task_tweet', 'task_id', 'tweet_id_str', 'id', 'id_str', 'tweets')
            ->using(TaskTweet::class)
            ->withPivot(['favorited', 'retweeted', 'removed', 'removed_task_id'])
            ->with('media.mediaFiles');
    }

    public function socialUser()
    {
        return $this->belongsTo(SocialUser::class, 'socialuser_id', 'id');
    }

    public function exports()
    {
        return $this->hasMany(Export::class, 'task_id', 'id');
    }

    public function view()
    {
        return $this->hasOne(TaskView::class, 'task_id', 'id');
    }

    public function followings()
    {
        return $this->hasMany(Following::class, 'task_id', 'id');
    }

    public function followers()
    {
        return $this->hasMany(Follower::class, 'task_id', 'id');
    }

    public function managedBy()
    {
        return $this->belongsTo(self::class, 'managed_by_task_id', 'id');
    }

    public function targetedTask()
    {
        return $this->belongsTo(self::class, 'targeted_task_id', 'id');
    }

    public function managedTasks()
    {
        return $this->hasMany(self::class, 'managed_by_task_id', 'id');
    }

    public function getBaseNameAttribute()
    {
        return strtolower(class_basename(substr($this->type, 0, -9)));
    }

    public function getComponentNameAttribute()
    {
        if (in_array($this->type, [ManagedDestroyLikesOperation::class, ManagedDestroyTweetsOperation::class])) {
            return 'managedtask';
        }

        return $this->baseName;
    }

    public function getShortNameAttribute()
    {
        return Str::slug(__('messages.'.$this->type, [], 'en'));
    }

    public function getRemovedCountAttribute()
    {
        if ($this->type === ManagedDestroyLikesOperation::class) {
            $managedTask = self::where('managed_by_task_id', $this->id)->where('type', DestroyLikesOperation::class)->first();

            return ($managedTask ?? optional())->removedCount;
        }

        if ($this->type === ManagedDestroyTweetsOperation::class) {
            $managedTask = self::where('managed_by_task_id', $this->id)->where('type', DestroyTweetsOperation::class)->first();

            return ($managedTask ?? optional())->removedCount;
        }

        if (! in_array($this->type, [DestroyLikesOperation::class, DestroyTweetsOperation::class])) {
            return;
        }

        if ($this->type === DestroyLikesOperation::class) {
            $targetedTask = $this->targetedTask;

            if (! $targetedTask) {
                return '?';
            }

            $likes = $targetedTask->likes;

            $removed = $likes->filter(function ($tweet) {
                return ! empty($tweet->pivot->removed);
            });

            $removeScopeCount = Arr::get($this->extra, 'removeScopeCount', '?');

            return $removed->where('pivot.removed_task_id', $this->id)->count().'/'.$removeScopeCount;
        }

        if ($this->type === DestroyTweetsOperation::class) {
            $targetedTask = $this->targetedTask;

            if (! $targetedTask) {
                return '?';
            }

            $tweets = $targetedTask->tweets;

            $removed = $tweets->filter(function ($tweet) {
                return ! empty($tweet->pivot->removed);
            });

            $removeScopeCount = Arr::get($this->extra, 'removeScopeCount', '?');

            return $removed->where('pivot.removed_task_id', $this->id)->count().'/'.$removeScopeCount;
        }
    }
}

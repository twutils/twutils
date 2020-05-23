<?php

namespace App;

use App\TwUtils\TwitterOperations\destroyLikesOperation;
use App\TwUtils\TwitterOperations\destroyTweetsOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Storage;

class Task extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['baseName', 'removedCount', 'componentName'];
    protected $hidden = ['exception'];
    protected $withCount = ['likes', 'followings', 'followers'];
    protected $casts = ['extra' => 'array'];

    public const TWEETS_LISTS_BASE_NAMES = [
        'fetchlikes',
        'fetchentitieslikes',
        'fetchusertweets',
        'fetchentitiesusertweets',
    ];

    public const USERS_LISTS_BASE_NAMES = [
        'fetchfollowing',
        'fetchfollowers',
    ];

    public const TWEETS_DESTROY_BASE_NAMES = [
        'destroytweets',
        'destroylikes',
    ];

    public const TWEETS_MANAGED_DESTROY_BASE_NAMES = [
        'manageddestroytweets',
        'manageddestroylikes',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $task) {
            $taskId = $task->id;
            $taskTweets = TaskTweet::where('task_id', $taskId)
                ->get()
                ->pluck('tweet_id_str');

            $taskTweeps = Following::where('task_id', $taskId)
                ->get()
                ->concat(
                    Follower::where('task_id', $taskId)->get()
                )
                ->pluck('tweep_id_str');

            TaskTweet::where('task_id', $taskId)->delete();
            Following::where('task_id', $taskId)->delete();
            Follower::where('task_id', $taskId)->delete();

            $disks = [
                'tasks',
                'temporaryTasks',
                'htmlTasks',
                config('filesystems.cloud'),
            ];

            foreach ($disks as $disk) {
                if (Storage::disk($disk)->exists($taskId)) {
                    Storage::disk($disk)->deleteDir($taskId);
                }
            }

            if (in_array($task->baseName, static::TWEETS_LISTS_BASE_NAMES)) {
                $tweetsSharedWithOtherTasks = TaskTweet::where('task_id', '!=', $taskId)
                    ->whereIn('tweet_id_str', $taskTweets)
                    ->get()
                    ->pluck('tweet_id_str');

                $taskTweetsWithoutSharedWithOtherTasksTweets = $taskTweets->diff($tweetsSharedWithOtherTasks);

                Tweet::whereIn('id_str', $taskTweetsWithoutSharedWithOtherTasksTweets)->get()->map->delete();
            } elseif (in_array($task->baseName, static::USERS_LISTS_BASE_NAMES)) {
                $tweepsSharedWithOtherTasks = Following::where('task_id', '!=', $taskId)
                    ->whereIn('tweep_id_str', $taskTweeps)
                    ->get()
                    ->concat(
                        Follower::where('task_id', '!=', $taskId)
                        ->whereIn('tweep_id_str', $taskTweeps)
                        ->get()
                    )
                    ->pluck('tweep_id_str');

                $taskTweepsWithoutSharedWithOtherTasksTweeps = $taskTweeps->diff($tweepsSharedWithOtherTasks);

                Tweep::whereIn('id_str', $taskTweepsWithoutSharedWithOtherTasksTweeps)->get()->map->delete();

                $task->managedTasks->map->delete();
            }
        });
    }

    public function getTaskTweeps()
    {
        $tweeps = collect([]);

        if (in_array($this->baseName, self::TWEETS_LISTS_BASE_NAMES)) {
            $tweeps = $this->likes
                        ->pluck('tweep')
                        ->unique('id');
        }

        if ($this->baseName === 'fetchfollowing') {
            $tweeps = $this->followings()
                    ->with('tweep')
                    ->get()
                    ->unique('id')
                    ->pluck('tweep');
        }

        if ($this->baseName === 'fetchfollowers') {
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
            ->withPivot(['favorited', 'retweeted', 'removed', 'removed_task_id', 'attachments']);
    }

    public function socialUser()
    {
        return $this->belongsTo(SocialUser::class, 'socialuser_id', 'id');
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
        if (!in_array($this->type, [destroyLikesOperation::class, destroyTweetsOperation::class])) {
            return null;
        }

        if ($this->type === destroyLikesOperation::class) {
            $targetedTask = self::find($this->extra['targeted_task_id']);

            if ( ! $targetedTask)
            {
                return '?';
            }

            $likes = $targetedTask->likes;

            $removed = $likes->filter(function ($tweet) {
                return !empty($tweet->pivot->removed);
            });

            $removeScopeCount = Arr::get($this->extra, 'removeScopeCount', '?');

            return $removed->where('pivot.removed_task_id', $this->id)->count().'/'.$removeScopeCount;
        }

        if ($this->type === destroyTweetsOperation::class) {
            $targetedTask = self::find($this->extra['targeted_task_id']);

            if ( ! $targetedTask)
            {
                return '?';
            }

            $tweets = $targetedTask->tweets;

            $removed = $tweets->filter(function ($tweet) {
                return !empty($tweet->pivot->removed);
            });

            $removeScopeCount = Arr::get($this->extra, 'removeScopeCount', '?');

            return $removed->where('pivot.removed_task_id', $this->id)->count().'/'.$removeScopeCount;
        }
    }
}

<?php

namespace App\TwUtils\TwitterOperations;

use App\Task;
use App\Tweep;
use App\Tweet;
use App\TaskTweet;
use Carbon\Carbon;
use App\Jobs\CleanLikesJob;
use App\Jobs\FetchLikesJob;
use App\Jobs\CleanTweepsJob;
use App\TwUtils\TweepsManager;
use App\TwUtils\TweetsManager;

class FetchLikesOperation extends TwitterOperation
{
    protected $endpoint = 'favorites/list';
    protected $scope = 'read';
    protected $httpMethod = 'get';
    protected $downloadTweetsWithMedia = false;

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];

        $last = (array) collect($this->response)->last();

        $parameters = $this->buildParameters();

        $parameters['max_id'] = $last['id_str'];

        dispatch(new FetchLikesJob($parameters, $this->socialUser, $this->task))->delay($nextJobDelay);
    }

    protected function shouldBuildNextJob()
    {
        $response = collect($this->response);

        $this->task = $this->task->fresh();

        if ($this->task->status != 'queued') {
            return false;
        }

        $shouldBuild = $response->count() >= config('twutils.minimum_expected_likes');

        if (! $shouldBuild) {
            $this->setCompletedTask($this->task);
        }

        return $shouldBuild;
    }

    protected function afterCompletedTask(Task $task)
    {
        dispatch(new CleanTweepsJob());
    }

    protected function saveResponse()
    {
        $taskId = $this->task['id'];
        $taskSettings = $this->task->extra['settings'];
        $likes = [];

        $responseCollection = collect($this->response)
            ->map(function ($tweet) {
                $tweet->created_at = Carbon::createFromTimestamp(strtotime($tweet->created_at ?? 1));

                return $tweet;
            });

        if ($taskSettings && ($taskSettings['start_date'] || $taskSettings['end_date'])) {
            if (isset($taskSettings['start_date'])) {
                $responseCollection = $responseCollection->filter(function ($tweet) use ($taskSettings) {
                    return $tweet->created_at->greaterThanOrEqualTo($taskSettings['start_date']);
                });
            }

            if (isset($taskSettings['end_date'])) {
                $responseCollection = $responseCollection->filter(function ($tweet) use ($taskSettings) {
                    return $tweet->created_at->lessThanOrEqualTo($taskSettings['end_date']);
                });
            }
        }

        if (count($this->response) === 0) {
            return;
        }

        $tweeps = $responseCollection->map(function ($tweet) {
            return $tweet->user;
        });

        $tweeps->chunk(config('twutils.database_groups_chunk_counts.tweep_db_where_in_limit'))
        ->each(function ($tweepsGroup) {
            $tweepsGroup = $tweepsGroup->map(function ($tweep) {
                return TweepsManager::mapResponseUserToTweep((array) $tweep);
            });
            Tweep::insert($tweepsGroup->toArray());
        });

        $responseCollection->each(
            function ($like) use (&$likes, $taskId) {
                $like = (array) json_decode(json_encode($like), true);

                $mappedTweet = TweetsManager::mapResponseToTweet($like, $taskId);
                array_push($likes, $mappedTweet);
            }
        );

        foreach (collect($likes)->chunk(config('twutils.database_groups_chunk_counts.fetch_likes')) as $i => $likesGroup) {
            Tweet::insert($likesGroup->toArray());
        }

        foreach (collect($likes)->chunk(config('twutils.database_groups_chunk_counts.fetch_likes')) as $i => $likesGroup) {
            $tweets = $likesGroup->map(function ($tweet) use ($responseCollection) {
                $id = $tweet['id_str'];
                $tweetResponse = $responseCollection->where('id_str', $id)->first();

                return ['favorited'=>$tweetResponse->favorited, 'retweeted' => $tweetResponse->retweeted, 'task_id' => $this->task->id, 'tweet_id_str' => $id];
            })->toArray();

            TaskTweet::insert($tweets);
        }

        dispatch(new CleanLikesJob($this->task));
    }

    protected function buildParameters()
    {
        $defaultParameters = [
            'count'            => config('twutils.twitter_requests_counts.fetch_likes'),
            'include_entities' => true,
            'screen_name'      => $this->socialUser->nickname,
            'tweet_mode'       => 'extended',
            'user_id'          => $this->socialUser->social_user_id,
        ];

        return array_merge($defaultParameters, $this->getParametersFromSettings());
    }

    protected function getParametersFromSettings()
    {
        $taskSettings = $this->task->extra['settings'];

        if ( empty($taskSettings))
        {
            return [];
        }

        $addedParameters = [];

        if ( isset($taskSettings['start_date'])) {
            $startDate = Carbon::createFromFormat('Y-m-d', $taskSettings['start_date']);

            $closestTweet = Tweet::where('tweet_created_at', '<=', $startDate->subDays(1))
                            ->orderByDesc('tweet_created_at')
                            ->limit(1)
                            ->first();
            if ($closestTweet) {
                $addedParameters['since_id'] = $closestTweet->id_str;
            }
        }

        if ( isset($taskSettings['end_date'])) {
            $startDate = Carbon::createFromFormat('Y-m-d', $taskSettings['end_date']);

            $closestTweet = Tweet::where('tweet_created_at', '>=', $startDate->addDays(1))
                            ->orderBy('tweet_created_at')
                            ->limit(1)
                            ->first();

            if ($closestTweet) {
                $addedParameters['max_id'] = $closestTweet->id_str;
            }

        }

        return $addedParameters;
    }

    public function dispatch()
    {
        $parameters = $this->buildParameters();
        $taskSettings = $this->task->extra['settings'];

        return dispatch(new FetchLikesJob($parameters, $this->socialUser, $this->task));
    }
}

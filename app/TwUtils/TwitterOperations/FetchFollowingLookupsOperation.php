<?php

namespace App\TwUtils\TwitterOperations;

use App\Models\Task;
use App\Jobs\CompleteTaskJob;
use App\Jobs\FetchFollowingLookupsJob;

class FetchFollowingLookupsOperation extends TwitterOperation
{
    protected $endpoint = 'friendships/lookup';
    protected $scope = 'read';
    protected $httpMethod = 'get';

    /*
    * $parameters contains 'index' key to indicate the index
    * to be used to slice the followings collection.
    */
    protected function handleJobParameters($parameters)
    {
        $followings = $this->task->followings;

        $filtered = $followings->slice($parameters['index'], config('twutils.twitter_requests_counts.fetch_following_lookups'));

        $filtered = $filtered->load('tweep')->pluck('tweep');

        $nextIndex = ($parameters['index'] + count($filtered));

        if ($nextIndex >= $followings->count()) {
            $nextIndex = -1;
        }

        $this->data = ['nextIndex' => $nextIndex];

        $this->parameters = ['user_id' => $filtered->implode('id_str', ',')];
    }

    protected function buildNextJob()
    {
        dispatch(new FetchFollowingLookupsJob(['index' => $this->data['nextIndex']], $this->socialUser, $this->task))->delay($this->data['nextJobDelay']);
    }

    protected function shouldBuildNextJob()
    {
        $this->task = $this->task->fresh();

        if ($this->data['nextIndex'] == -1) {
            $this->setCompletedTask($this->task);

            return false;
        }

        return true;
    }

    protected function setCompletedTask($task)
    {
        dispatch(new CompleteTaskJob($task));
        $this->afterCompletedTask($task);
    }

    protected function afterCompletedTask(Task $task)
    {
    }

    protected function saveResponse()
    {
        $followedByOnly = collect($this->response)->filter(
            function ($lookup, $key) {
                $lookup = (array) $lookup;

                return in_array('followed_by', $lookup['connections']);
            }
        );
        foreach ($followedByOnly->chunk(config('twutils.database_groups_chunk_counts.fetch_following_lookups')) as $i => $lookupGroup) {
            $followedByIds = $lookupGroup->pluck('id_str')->toArray();

            $this->task->followings()
            ->whereIn('tweep_id_str', $followedByIds)
            ->update(['followed_by' => true]);
        }
    }

    protected function buildParameters()
    {
        return [];
    }

    public function dispatch()
    {
        return dispatch(new FetchFollowingLookupsJob($this->doRequestParameters, $this->socialUser, $this->task))->delay($this->data['nextJobDelay']);
    }
}

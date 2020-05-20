<?php

namespace App\TwUtils\TwitterOperations;

use App\Jobs\CleanLikesJob;
use App\Jobs\FetchEntitiesUserTweetsJob;
use App\Jobs\ZipEntitiesJob;
use App\SocialUser;
use App\Task;
use App\Tweet;
use App\TwUtils\AssetsManager;
use App\User;
use Auth;
use Carbon\Carbon;

class FetchEntitiesUserTweetsOperation extends FetchEntitiesLikesOperation
{
    protected $endpoint = 'statuses/user_timeline';
    protected $scope = 'read';

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];

        $last = (array) collect($this->response)->last();

        $parameters = $this->buildParameters() + ['max_id' => $last['id_str']];

        dispatch(new FetchEntitiesUserTweetsJob($parameters, $this->socialUser, $this->task))->delay($nextJobDelay);
    }

    public function dispatch()
    {
        $parameters = $this->buildParameters();

        try {
            return dispatch(new FetchEntitiesUserTweetsJob($parameters, $this->socialUser, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }
}

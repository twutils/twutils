<?php

namespace App\TwUtils\TwitterOperations;

use App\Jobs\FetchUserTweetsJob;

class FetchUserTweetsOperation extends FetchLikesOperation
{
    protected $endpoint = 'statuses/user_timeline';
    protected $scope = 'read';

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];

        $last = (array) collect($this->response)->last();

        $parameters = $this->buildParameters() + ['max_id' => $last['id_str']];

        dispatch(new FetchUserTweetsJob($parameters, $this->socialUser, $this->task))->delay($nextJobDelay);
    }

    public function dispatch()
    {
        $parameters = $this->buildParameters();

        try {
            return dispatch(new FetchUserTweetsJob($parameters, $this->socialUser, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }

    protected function buildParameters()
    {
        return [
            'user_id'          => $this->socialUser->social_user_id,
            'screen_name'      => $this->socialUser->nickname,
            'count'            => config('twutils.twitter_requests_counts.fetch_likes'),
            'exclude_replies'  => false,
            'include_rts'      => true,
            'trim_user'        => false,
            'include_entities' => true,
            'tweet_mode'       => 'extended',
        ];
    }
}

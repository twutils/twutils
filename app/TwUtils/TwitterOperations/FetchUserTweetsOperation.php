<?php

namespace App\TwUtils\TwitterOperations;

use App\Jobs\FetchUserTweetsJob;

class FetchUserTweetsOperation extends FetchLikesOperation
{
    protected $shortName = 'UserTweets';

    protected $endpoint = 'statuses/user_timeline';

    protected $scope = 'read';

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];

        $last = (array) collect($this->response)->last();

        $parameters = $this->buildParameters();

        $parameters['max_id'] = $last['id_str'];

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
        $defaultParameters = [
            'count'            => config('twutils.twitter_requests_counts.fetch_likes'),
            'exclude_replies'  => false,
            'include_entities' => true,
            'include_rts'      => true,
            'screen_name'      => $this->socialUser->nickname,
            'trim_user'        => false,
            'tweet_mode'       => 'extended',
            'user_id'          => $this->socialUser->social_user_id,
        ];

        return array_merge($defaultParameters, $this->getParametersFromSettings());
    }
}

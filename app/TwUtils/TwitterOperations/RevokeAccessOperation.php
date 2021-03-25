<?php

namespace App\TwUtils\TwitterOperations;

use App\Models\Task;

class RevokeAccessOperation extends TwitterOperation
{
    protected $endpoint = 'oauth/invalidate_token';

    protected $scope = 'read';

    protected $httpMethod = 'post';

    protected function shouldBuildNextJob()
    {
        return false;
    }

    protected function buildNextJob()
    {
    }

    protected function handleTwitterException($e)
    {
    }

    protected function afterCompletedTask(Task $task)
    {
    }

    protected function saveResponse()
    {
        $socialUser = $this->socialUser;

        $response = $this->response;

        $socialUser->token = '';
        $socialUser->token_secret = '';
        $socialUser->save();
    }

    protected function buildParameters()
    {
        return [
            'access_token'        => $this->socialUser->token,
            'access_token_secret' => $this->socialUser->token_secret,
        ];
    }

    public function dispatch()
    {
        $parameters = $this->buildParameters();

        $this->doRequest($this->socialUser, $this->task, $parameters);
    }
}

<?php

namespace AppNext\Jobs;

use AppNext\Twitter\Api\Requester;

class DestroyRawTweetJob extends DestroyRawLikeJob
{
    protected function run(): void
    {
        try {
            $response = Requester::for($this->task->socialUser)
                ->destroyTweet(
                    $this->rawTweet->id_str
                );
            $this->success($response);
        } catch (\Exception $e) {
            $this->failure($e);
        }
    }
}

<?php

namespace AppNext\Jobs;

class DestroyRawTweetJob extends DestroyRawLikeJob
{
    protected function run(): void
    {
        try {
            $response = $this->getTwitterInstance()
                ->destroyTweet(
                    $this->rawTweet->id_str
                );
            $this->success($response);
        } catch (\Exception $e) {
            $this->failure($e);
        }
    }
}

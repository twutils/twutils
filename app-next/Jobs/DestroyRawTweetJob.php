<?php

namespace AppNext\Jobs;

use App\Utils;
use Exception;
use App\Models\Task;
use App\Models\RawTweet;
use App\TwUtils\Base\Job;
use Atymic\Twitter\Facade\Twitter;
use AppNext\Tasks\DestroyLikesByUpload;
use AppNext\Tasks\DestroyTweetsByUpload;
use Atymic\Twitter\ApiV1\Contract\Twitter as TwitterV1Contract;

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

<?php

namespace AppNext\Jobs;

use App\Utils;
use Exception;
use App\Models\Task;
use App\Models\RawTweet;
use App\TwUtils\Base\Job;
use AppNext\Twitter\Requester;
use AppNext\Tasks\DestroyLikesByUpload;
use AppNext\Tasks\DestroyTweetsByUpload;

class DestroyRawLikeJob extends Job
{
    public function __construct(
        protected Task $task,
        protected RawTweet $rawTweet
    ) {
    }

    public function handle()
    {
        Utils::setup_twitter_config_for_read_write();

        $this->run();
    }

    protected function success($response): void
    {
        $this->rawTweet->update([
            'removed' => now(),
        ]);

        /** @var DestroyTweetsByUpload|DestroyLikesByUpload * */
        $operationInstance = $this->task->getTaskTypeInstance();

        $operationInstance->run();
    }

    protected function failure(Exception $e): void
    {
        dd($e);
    }

    protected function run(): void
    {
        try {
            $response = Requester::for($this->task->socialUser)
                ->destroyFavorite([
                    'id' => $this->rawTweet->id_str,
                ]);
            $this->success($response);
        } catch (\Exception $e) {
            $this->failure($e);
        }
    }
}

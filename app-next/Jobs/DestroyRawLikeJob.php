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
            $response = $this->getTwitterInstance()
                ->destroyFavorite([
                    'id' => $this->rawTweet->id_str,
                ]);
            $this->success($response);
        } catch (\Exception $e) {
            $this->failure($e);
        }
    }

    protected function getTwitterInstance(): TwitterV1Contract
    {
        return Twitter::usingCredentials(
            $this->task->socialUser->token,
            $this->task->socialUser->token_secret,
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
        )
        ->forApiV1();
    }
}

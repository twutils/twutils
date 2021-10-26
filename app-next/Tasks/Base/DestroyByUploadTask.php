<?php

namespace AppNext\Tasks\Base;

use App\Models\RawTweet;
use AppNext\Tasks\Config;

abstract class DestroyByUploadTask extends Task
{
    final public function init(): void
    {
        $this->taskModel->getChosenUpload()->rawTweets()->update([
            'removed' => null,
        ]);

        $this->run();
    }

    final public function run(): void
    {
        $tweetQuery = $this->taskModel->getChosenUpload()->rawTweets()->where('removed', '=', null);

        if (! $tweetQuery->exists()) {
            $this->taskModel->update([
                'status' => 'completed',
            ]);

            return;
        }

        $this->destroyRawTweet($tweetQuery->first());
    }

    final protected function destroyRawTweet(RawTweet $rawTweet): void
    {
        dispatch(
            new (Config::getJob($this::class))(
                $this->taskModel,
                $rawTweet
            )
        );
    }
}

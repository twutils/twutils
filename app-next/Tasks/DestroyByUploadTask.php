<?php

namespace AppNext\Tasks;

use App\Models\RawTweet;

abstract class DestroyByUploadTask extends Base
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

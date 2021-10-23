<?php

namespace AppNext\Tasks\Base;

use App\Models\RawTweet;

abstract class DestroyByUploadTask extends UploadTask
{
    public function init(): void
    {
        $this->taskModel->getChosenUpload()->rawTweets()->update([
            'removed' => null,
        ]);

        $this->run();
    }

    public function run(): void
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

    abstract protected function destroyRawTweet(RawTweet $rawTweet);
}

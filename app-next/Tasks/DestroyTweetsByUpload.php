<?php

namespace AppNext\Tasks;

use AppNext\Tasks\Base\UploadTask;
use AppNext\Jobs\DestroyRawTweetJob;

class DestroyTweetsByUpload extends UploadTask
{
    protected string $shortName = 'ManagedDestroyTweets';

    protected array $acceptsUploadPurpose = [
        'DestroyTweets',
    ];

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

        dispatch(
            new DestroyRawTweetJob(
                $this->taskModel,
                $tweetQuery->first()
            )
        );
    }
}

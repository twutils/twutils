<?php

namespace AppNext\Tasks;

use App\Models\RawTweet;
use AppNext\Tasks\Base\UploadTask;
use AppNext\Jobs\DestroyRawLikeJob;

class DestroyLikesByUpload extends UploadTask
{
    protected string $shortName = 'ManagedDestroyLikes';

    protected array $acceptsUploadPurpose = [
        'DestroyLikes',
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

        $this->destroyRawTweet($tweetQuery->first());
    }

    protected function destroyRawTweet(RawTweet $rawTweet)
    {
        dispatch(
            new DestroyRawLikeJob(
                $this->taskModel,
                $rawTweet
            )
        );
    }
}

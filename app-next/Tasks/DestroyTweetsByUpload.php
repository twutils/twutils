<?php

namespace AppNext\Tasks;

use App\Models\RawTweet;
use AppNext\Jobs\DestroyRawTweetJob;

class DestroyTweetsByUpload extends DestroyLikesByUpload
{
    protected string $shortName = 'ManagedDestroyTweets';

    protected array $acceptsUploadPurpose = [
        'DestroyTweets',
    ];

    protected function destroyRawTweet(RawTweet $rawTweet)
    {
        dispatch(
            new DestroyRawTweetJob(
                $this->taskModel,
                $rawTweet
            )
        );
    }
}

<?php

namespace AppNext\Tasks;

use App\Models\RawTweet;
use AppNext\Jobs\DestroyRawTweetJob;
use AppNext\Tasks\Base\DestroyByUploadTask;

class DestroyTweetsByUpload extends DestroyByUploadTask
{
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

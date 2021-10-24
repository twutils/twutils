<?php

namespace AppNext\Tasks;

use App\Models\RawTweet;
use AppNext\Jobs\DestroyRawLikeJob;
use AppNext\Tasks\Base\DestroyByUploadTask;

class DestroyLikesByUpload extends DestroyByUploadTask
{
    protected array $acceptsUploadPurpose = [
        'DestroyLikes',
    ];

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

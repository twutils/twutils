<?php

namespace App\TwUtils\TwitterOperations;

use App\Models\Upload;

abstract class UploadRawTwitterOperation extends TwitterOperation
{
    protected array $acceptsUploadPurpose;

    final public function acceptsUpload(Upload $upload): bool
    {
        return in_array($upload->purpose, $this->acceptsUploadPurpose);
    }

    public function dispatch()
    {
        dispatch(
            new DestroyRawTweetJob(
                $this->task,
                $this->task->getChosenUpload()->rawTweets()->where('removed', '=', null)->first()->id,
            )
        );
    }
}

<?php

namespace App\TwUtils\TwitterOperations;

class ManagedDestroyTweetsOperationByUpload extends UploadRawTwitterOperation
{
    protected $shortName = 'ManagedDestroyTweets';

    protected $scope = 'write';

    protected array $acceptsUploadPurpose = ['DestroyTweets'];
}

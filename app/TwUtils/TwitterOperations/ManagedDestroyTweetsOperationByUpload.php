<?php

namespace App\TwUtils\TwitterOperations;

use App\Models\Task;
use App\Models\SocialUser;
use App\Jobs\CompleteTaskJob;
use App\Jobs\CompleteManagedDestroyTweetsJob;
use App\Models\Upload;
use App\TwUtils\Tasks\Factory as TaskFactory;
use App\TwUtils\Tasks\Validators\DateValidator;

class ManagedDestroyTweetsOperationByUpload extends UploadRawTwitterOperation
{
    protected $shortName = 'ManagedDestroyTweets';

    protected $scope = 'write';

    protected array $acceptsUploadPurpose = ['DestroyTweets'];
}

<?php

namespace App\TwUtils\TwitterOperations;

use App\Models\Task;
use App\Models\SocialUser;
use App\Jobs\CompleteTaskJob;
use App\Jobs\CompleteManagedDestroyLikesJob;
use App\Models\Upload;
use App\TwUtils\Tasks\Factory as TaskFactory;
use App\TwUtils\Tasks\Validators\DateValidator;

class ManagedDestroyLikesOperationByUpload extends UploadRawTwitterOperation
{
    protected $shortName = 'ManagedDestroyLikes';

    protected $scope = 'write';

    protected array $acceptsUploadPurpose = ['DestroyLikes'];
}

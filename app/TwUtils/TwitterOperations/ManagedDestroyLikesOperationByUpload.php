<?php

namespace App\TwUtils\TwitterOperations;

class ManagedDestroyLikesOperationByUpload extends UploadRawTwitterOperation
{
    protected $shortName = 'ManagedDestroyLikes';

    protected $scope = 'write';

    protected array $acceptsUploadPurpose = ['DestroyLikes'];
}

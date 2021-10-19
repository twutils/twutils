<?php

namespace AppNext\Tasks;

use AppNext\Tasks\Base\UploadTask;

class DestroyLikesByUpload extends UploadTask
{
    protected string $shortName = 'ManagedDestroyLikes';

    protected array $acceptsUploadPurpose = [
        'DestroyLikes',
    ];

    public function init(): void
    {
    }

    public function run(): void
    {
    }
}

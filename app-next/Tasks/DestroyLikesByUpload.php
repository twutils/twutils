<?php

namespace AppNext\Tasks;

use AppNext\Base\Task;

class DestroyLikesByUpload extends Task
{
    protected string $scope = 'write';

    protected string $shortName = 'ManagedDestroyLikes';

    protected array $acceptsUploadPurpose = [
        'DestroyLikes',
    ];

    public function init(): void
    {
    }
}

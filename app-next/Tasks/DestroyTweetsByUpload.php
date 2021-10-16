<?php

namespace AppNext\Tasks;

use AppNext\Base\Task;

class DestroyTweetsByUpload extends Task
{
    protected string $scope = 'write';

    protected string $shortName = 'ManagedDestroyTweets';

    protected array $acceptsUploadPurpose = [
        'DestroyTweets',
    ];

    public function init(): void
    {
        $this->task->getChosenUpload()->rawTweets()->update([
            'removed' => null
        ]);

        // TODO
        // dd('here');
    }
}

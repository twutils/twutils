<?php

namespace AppNext\Tasks\Base;

use App\Models\Upload;
use AppNext\Tasks\Config;

abstract class UploadTask extends Task
{
    protected string $scope = 'write';

    final public function acceptsUpload(Upload $upload): bool
    {
        return in_array($upload->purpose, Config::getUploadPurposes($this::class));
    }
}

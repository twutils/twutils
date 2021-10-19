<?php

namespace AppNext\Tasks\Base;

use App\Models\Upload;

abstract class UploadTask extends Task
{
    protected string $scope = 'write';

    protected array $acceptsUploadPurpose;

    final public function acceptsUpload(Upload $upload): bool
    {
        return in_array($upload->purpose, $this->acceptsUploadPurpose);
    }
}

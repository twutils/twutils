<?php

namespace AppNext\Base;

use App\Models\Task as TaskModel;
use App\Models\Upload;

abstract class Task
{
    protected string $scope;

    protected string $shortName;

    protected array $acceptsUploadPurpose;

    final public function __construct(
        protected TaskModel $task
    )
    {
    }

    abstract public function init(): void;

    final public function getScope(): string
    {
        return $this->scope;
    }

    final public function getShortName(): string
    {
        return $this->shortName;
    }

    final public function acceptsUpload(Upload $upload): bool
    {
        return in_array($upload->purpose, $this->acceptsUploadPurpose);
    }
}

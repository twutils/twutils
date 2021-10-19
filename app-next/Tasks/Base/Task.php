<?php

namespace AppNext\Tasks\Base;

use App\Models\Task as TaskModel;

abstract class Task
{
    protected string $scope;

    protected string $shortName;

    final public function __construct(
        protected TaskModel $taskModel
    ) {
    }

    abstract public function init(): void;

    abstract public function run(): void;

    final public function getScope(): string
    {
        return $this->scope;
    }

    final public function getShortName(): string
    {
        return $this->shortName;
    }
}

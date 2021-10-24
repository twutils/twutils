<?php

namespace AppNext\Tasks\Base;

use App\Models\Task as TaskModel;

abstract class Task
{
    final public function __construct(
        protected TaskModel $taskModel
    ) {
    }

    abstract public function init(): void;

    abstract public function run(): void;
}

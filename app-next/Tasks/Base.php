<?php

namespace AppNext\Tasks;

use App\Models\Task as TaskModel;

abstract class Base
{
    final public function __construct(
        protected TaskModel $taskModel
    ) {
    }

    abstract public function init(): void;

    abstract public function run(): void;
}

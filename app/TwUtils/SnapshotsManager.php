<?php

namespace App\TwUtils;

use App\SocialUser;
use App\Task;

class SnapshotsManager
{
    public $operationInstance;
    public $socialUser;
    public $data;

    public static function take(string $operation)
    {
        $self = new self();
        $operationClass = "App\TwUtils\TwitterOperations\\$operation".'Operation';

        $self->operationInstance = new $operationClass();

        return $self;
    }

    public function for(SocialUser $socialUser)
    {
        $this->socialUser = $socialUser;

        return $this;
    }

    public function with($data)
    {
        $this->data = $data;

        return $this;
    }

    public function get()
    {
        if (app('env') == 'testing') {
            $this->socialUser = $this->socialUser->fresh();
        }

        $task = Task::create(
            [
                'socialuser_id'      => $this->socialUser->id,
                'type'               => get_class($this->operationInstance),
                'status'             => 'queued',
                'extra'              => $this->data,
                'managed_by_task_id' => $this->data['managedByTaskId'] ?? null,
            ]
        );
        $this->operationInstance
        ->setSocialUser($this->socialUser)
        ->setTask($task)
        ->setData($this->data)
        ->dispatch();
        $this->operationInstance->initJob();

        return ['ok' => true, 'task_id' => $task->id];
    }
}

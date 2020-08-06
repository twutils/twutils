<?php

namespace App\Http\Requests;

use App\Task;
use App\TwUtils\UserManager;
use App\Exceptions\TaskAddException;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use App\TwUtils\TaskAdd\Factory as TaskFactory;

class TaskAddRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $targetedTask = ucfirst($this->segment(2));

        $relatedTask = Task::find($this->segment(3)) ?? (Task::find($this->id) ?? null);

        $taskFullType = collect(Task::AVAILABLE_OPERATIONS)->first(function ($operationClassName) use ($targetedTask) {
            return $targetedTask === (new $operationClassName)->getShortName();
        });

        $this->merge([
            'targetedTask'      => $targetedTask,
            'taskFullType'  => $taskFullType,
            'relatedTask'       => $relatedTask,
            'settings'          => $this->settings ?? [],
        ]);
    }

    public function authorize()
    {
        if ($this->user()->cannot('create', Task::class)) {
            throw new TaskAddException([__('messages.task_add_max_number')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return true;
    }

    public function rules()
    {
        return [
            'targetedTask' => [
                'bail',
                function ($attribute, $value, $fail) {
                    if (! $this->taskFullType) {
                        throw new TaskAddException([__('messages.task_add_bad_request')], Response::HTTP_BAD_REQUEST);
                    }
                },
                function ($attribute, $value, $fail) {
                    $scope = (new $this->taskFullType)->getScope();

                    $socialUser = UserManager::resolveUser($this->user(), $scope);

                    if ($socialUser == null) {
                        throw new TaskAddException([__('messages.task_add_no_privilege')], Response::HTTP_UPGRADE_REQUIRED);
                    }
                },
            ],
            'relatedTask' => [
                'bail',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $this->user()->cannot('view', $value)) {
                        throw new TaskAddException([__('messages.task_add_unauthorized_access')], Response::HTTP_UNAUTHORIZED);
                    }
                },
                function ($attribute, $value, $fail) {
                    if (($this->segment(3) || $this->has('id')) && $value === null) {
                        throw new TaskAddException([__('messages.task_add_target_not_found')], Response::HTTP_UNAUTHORIZED);
                    }
                },
            ],
            'taskFullType' => [
                'bail',
                function ($attribute, $value, $fail) {
                    $oldTasks = Task::whereIn('socialuser_id', $this->user()->socialUsers->pluck('id')->toArray())
                    ->where('type', $value)
                    ->where('status', 'queued')
                    ->get();

                    if ($oldTasks->count() != 0) {
                        throw new TaskAddException([], Response::HTTP_UNPROCESSABLE_ENTITY, ['task_id' => $oldTasks->last()->id]);
                    }
                },
                function ($attribute, $value, $fail) {
                    foreach ((new $value)->getValidators() as $validatorClassName) {
                        (new $validatorClassName)->apply($this->all(), $this->user());
                    }
                },
            ],
        ];
    }
}

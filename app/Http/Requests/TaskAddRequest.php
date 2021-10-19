<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Models\Upload;
use App\TwUtils\UserManager;
use App\Exceptions\TaskAddException;
use App\TwUtils\Services\TasksService;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use AppNext\Tasks\Base\Task as NextTwitterOperation;

class TaskAddRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $targetedTask = ucfirst($this->segment(2));

        $relatedTask = Task::find($this->segment(3)) ?? (Task::find($this->id) ?? null);

        $taskFullType = app(TasksService::class)
            ->findOperationTypeByShortName(
                $targetedTask,
                $this->wantsUploadsTask(),
            );

        $this->merge([
            'targetedTask'      => $targetedTask,
            'taskFullType'      => $taskFullType,
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
            'settings' => [
                'nullable',
                'array',
                // Validate correct tweets source settings
                function ($attribute, $value, $fail) {
                    // .. When it's file, Validate chosen upload is required and belongs to user
                    if ($this->wantsUploadsTask()) {
                        validator()->make($value, [
                            'chosenUpload' => [
                                'required',
                                'integer',
                                \Illuminate\Validation\Rule::exists('uploads', 'id')
                                    ->where('user_id', $this->user()->id),
                            ],
                        ])
                        ->validate();
                    }
                },
            ],
            'targetedTask' => [
                'bail',
                // Validate task type
                function ($attribute, $value, $fail) {
                    if (! $this->taskFullType) {
                        throw new TaskAddException([__('messages.task_add_bad_request')], Response::HTTP_BAD_REQUEST);
                    }
                },
                // Validate has the proper scope token
                function ($attribute, $value, $fail) {
                    $scope = (new Task(['type' => $this->taskFullType]))->getTaskTypeInstance()->getScope();

                    $socialUser = app(UserManager::class)->resolveUser($this->user(), $scope);

                    if ($socialUser == null) {
                        throw new TaskAddException([__('messages.task_add_no_privilege')], Response::HTTP_UPGRADE_REQUIRED);
                    }
                },
                // Validate 'destroy using archive file' tasks
                function ($attribute, $value, $fail) {
                    if (! $this->wantsUploadsTask()) {
                        return;
                    }

                    $chosenUpload = Upload::findOrFail($this->settings['chosenUpload']);

                    // Validate the chosen upload has the correct purpose
                    if (! (new Task(['type' => $this->taskFullType]))->getTaskTypeInstance()->acceptsUpload($chosenUpload)) {
                        throw new TaskAddException([__('messages.task_add_upload_wrong_purpose')], Response::HTTP_UPGRADE_REQUIRED);
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
                    $operation = (new Task(['type' => $this->taskFullType]))->getTaskTypeInstance();

                    if ($operation instanceof NextTwitterOperation) {
                        return;
                    }

                    foreach ((new $value)->getValidators() as $validatorClassName) {
                        (new $validatorClassName)->apply($this->all(), $this->user());
                    }
                },
            ],
        ];
    }

    protected function wantsUploadsTask(): bool
    {
        return ($this->settings['tweetsSource'] ?? null) === 'file';
    }
}

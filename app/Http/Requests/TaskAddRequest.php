<?php

namespace App\Http\Requests;

use App\Task;
use App\TwUtils\TasksAdder;
use App\Exceptions\TaskAddException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\TwUtils\TwitterOperations\TwitterOperation;
use App\TwUtils\UserManager;

class TaskAddRequest extends FormRequest
{
    /**
     * inherited
     */
/*    public function validateResolved()
    {
        $this->prepareForValidation();

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        $instance = $this->getValidatorInstance();

        if ($instance->fails()) {
            $this->failedValidation($instance);
        }

        $this->passedValidation();
    }*/

    protected function prepareForValidation()
    {
        $targetedTask = ucfirst($this->segment(2));

        $relatedTask = Task::find($this->segment(3)) ?? (Task::find($this->id) ?? null);


        $targetedFullType = collect(TasksAdder::$availableTasks)->first(function ($operationClassName) use ($targetedTask) {
            return $targetedTask === (new $operationClassName)->getShortName();
        });

        $this->merge([
            'targetedTask'      => $targetedTask,
            'targetedFullType'  => $targetedFullType,
            'relatedTask'       => $relatedTask,
        ]);
    }

    public function authorize()
    {
        if ( $this->user()->cannot('create', Task::class))
        {
            throw new TaskAddException([__('messages.task_add_max_number')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

        });
    }

    protected function failedAuthorization()
    {
        throw new TaskAddException(['Unhandled Error'], Response::HTTP_BAD_REQUEST);
    }

    protected function failedValidation(Validator $validator)
    {
        $this->failedAuthorization();
    }

    public function rules()
    {
        return [
            'targetedTask' => [
                'bail',
                function ($attribute, $value, $fail) {
                    if (! $this->targetedFullType)
                    {
                        throw new TaskAddException([__('messages.task_add_bad_request')], Response::HTTP_BAD_REQUEST);
                    }
                },
                function ($attribute, $value, $fail) {
                    $scope = (new $this->targetedFullType)->getScope();

                    $socialUser = UserManager::resolveUser($this->user(), $scope);

                    if ($socialUser == null) {
                        throw new TaskAddException([__('messages.task_add_no_privilege')], Response::HTTP_UPGRADE_REQUIRED);
                    }
                },
            ],
            'relatedTask' => [
                'bail',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $this->user()->cannot('view', $value))
                    {
                        throw new TaskAddException([__('messages.task_add_unauthorized_access')], Response::HTTP_UNAUTHORIZED);
                    }
                },
                function ($attribute, $value, $fail) {
                    if (($this->segment(3) || $this->has('id')) && $value === null)
                    {
                        throw new TaskAddException([__('messages.task_add_target_not_found')], Response::HTTP_UNAUTHORIZED);
                    }
                },
            ],
            'targetedFullType' => [
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
                    foreach((new $value)->getValidators() as $validatorClassName)
                    {
                        (new $validatorClassName)->apply($this->all(), $this->user());
                    }
                },
            ],
        ];
    }
}

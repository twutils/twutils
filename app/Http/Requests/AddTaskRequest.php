<?php

namespace App\Http\Requests;

use App\Task;
use App\TwUtils\TasksAdder;
use App\Exceptions\TaskAddException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

class AddTaskRequest extends FormRequest
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

        $this->merge([
            'targetedTask' => $targetedTask,
            'relatedTask'  => $relatedTask,
        ]);
    }

    public function authorize()
    {
        $hasMaximumTasks = Task::whereIn('socialuser_id', $this->user()->socialUsers->pluck('id')->toArray())
          ->where('managed_by_task_id', null)
          ->count() >= config('twutils.tasks_limit_per_user');

        if ($hasMaximumTasks)
        {
            throw new TaskAddException([__('messages.task_add_max_number')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
/*            dd(get_class($validator));
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }*/
        });
    }

    protected function failedAuthorization()
    {
        throw new TaskAddException([], 123);
    }

    protected function failedValidation(Validator $validator)
    {
        if (isset(($messages = $validator->errors()->messages())['targetedTask'] ))
        {
            $targetedTaskError = $messages['targetedTask'];

            throw new TaskAddException([$targetedTaskError[0]], $targetedTaskError[1]);
        }

        if (isset($messages['relatedTask'] ))
        {
            $relatedTaskError = $messages['relatedTask'];

            throw new TaskAddException([$relatedTaskError[0]], $relatedTaskError[1]);
        }
    }

    public function rules()
    {
        return [
            'targetedTask' => [
                function ($attribute, $value, $fail) {
                    $availableTasks = TasksAdder::getAvailableTasks();

                    if (! in_array($value, $availableTasks)) {
                        $fail([__('messages.task_add_bad_request'), Response::HTTP_BAD_REQUEST]);
                    }
                },
            ],
            'relatedTask' => [
                function ($attribute, $value, $fail) {
                    if ($value !== null && $this->user()->cannot('view', $value))
                    {
                        $fail([__('messages.task_add_unauthorized_access'), Response::HTTP_UNAUTHORIZED]);
                    }
                }
            ],
        ];
    }
}

<?php

namespace App\TwUtils;

use App\SocialUser;
use App\Task;
use App\User;
use Symfony\Component\HttpFoundation\Response;
use App\TwUtils\TwitterOperations\TwitterOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;

class TasksAdder
{
    protected $availableTasks = [
        'Likes'                => ['operation' => 'FetchLikes'],
        'EntitiesLikes'        => ['operation' => 'FetchEntitiesLikes'],
        'UserTweets'           => ['operation' => 'FetchUserTweets'],
        'EntitiesUserTweets'   => ['operation' => 'FetchEntitiesUserTweets'],
        'Following'            => ['operation' => 'FetchFollowing'],
        'Followers'            => ['operation' => 'FetchFollowers'],
        'DestroyLikes'         => ['operation' => 'destroyLikes'],
        'ManagedDestroyLikes'  => ['operation' => 'ManagedDestroyLikes'],
        'ManagedDestroyTweets' => ['operation' => 'ManagedDestroyTweets'],
        'DestroyTweets'        => ['operation' => 'destroyTweets'],
    ];
    protected $user;
    protected $socialUser;

    protected $targetedTask;
    protected $requestData;
    protected $relatedTask;
    protected $ok;
    protected $errors;
    protected $data;
    protected $statusCode;
    protected $managedByTaskId;

    public function __construct(string $targetedTask, array $requestData, Task $relatedTask = null, User $user)
    {
        $this->ok = false;
        $this->errors = [];
        $this->data = [];
        $this->statusCode = Response::HTTP_BAD_REQUEST;
        $this->managedByTaskId = $requestData['managedByTaskId'] ?? null;

        $this->targetedTask = ucfirst($targetedTask);
        $this->requestData = $requestData;
        $this->relatedTask = $relatedTask;
        $this->user = $user;

        // Build Task

        if (! $this->validRequest()) {
            return;
        }

        if (! $this->validTasksLimit()) {
            return;
        }

        $taskValidation = $this->{'validate'.$this->targetedTask}();

        if (! $taskValidation) {
            return;
        }

        $operationName = $this->availableTasks[$this->targetedTask]['operation'];

        $operationClassName = TwitterOperation::getClassName($operationName);

        $socialUser = $this->resolveUser(TwitterOperation::getOperationScope($operationName));

        if ($socialUser == null) {
            $this->ok = false;
            $this->errors = [__('messages.task_add_no_privilege')];
            $this->statusCode = Response::HTTP_UPGRADE_REQUIRED;

            return;
        }

        if ($this->hasPreviousTask($operationClassName)) {
            return;
        }

        $this->addTask($socialUser, $operationClassName);
    }


    public function validRequest()
    {
        if (! in_array($this->targetedTask, $this->getAvailableTasks())) {
            $this->ok = false;
            $this->errors = [__('messages.task_add_bad_request')];
            $this->statusCode = Response::HTTP_BAD_REQUEST;

            return false;
        }

        if ($this->relatedTask != null && ! $this->user->can('view', $this->relatedTask)) {
            $this->ok = false;
            $this->errors = [__('messages.task_add_unauthorized_access')];
            $this->statusCode = Response::HTTP_UNAUTHORIZED;

            return false;
        }

        return true;
    }

    public function validTasksLimit()
    {
        $hasMaximumTasks = Task::whereIn('socialuser_id', $this->user->socialUsers->pluck('id')->toArray())
          ->where('managed_by_task_id', null)
          ->count() >= config('twutils.tasks_limit_per_user');

        if ($hasMaximumTasks) {
            $this->ok = false;
            $this->statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $this->errors = [__('messages.task_add_max_number')];

            return false;
        }

        return true;
    }

    public function validateLikes()
    {
        return $this->validateUserTweets();
    }

    public function validateEntitiesLikes()
    {
        return true;
    }

    public function validateUserTweets()
    {
        $hasValidDates = $this->hasValidDates();

        if (! $hasValidDates['ok']) {
            $this->ok = $hasValidDates['ok'];
            $this->errors = $hasValidDates['errors'];
            $this->statusCode = $hasValidDates['statusCode'];

            return false;
        }

        return true;
    }

    public function validateEntitiesUserTweets()
    {
        return true;
    }

    public function validateFollowing()
    {
        return true;
    }

    public function validateFollowers()
    {
        return true;
    }

    public function validateDestroyLikes()
    {
        return $this->validateDestroyTweets();
    }

    public function validateManagedDestroyLikes()
    {
        $hasValidDates = $this->hasValidDates();

        if (! $hasValidDates['ok']) {
            $this->ok = $hasValidDates['ok'];
            $this->errors = $hasValidDates['errors'];
            $this->statusCode = $hasValidDates['statusCode'];

            return false;
        }

        return true;
    }

    public function validateManagedDestroyTweets()
    {
        return $this->validateManagedDestroyLikes();
    }

    public function hasValidDates()
    {
        $settings = $this->requestData['settings'] ?? null;
        // TODO: Potential bug on PHP 7.4 if $settings is null
        $startDate = $settings['start_date'] ?? null;
        $endDate = $settings['end_date'] ?? null;

        $shouldValidate = $endDate !== null || $startDate !== null;

        $datesErrors = validator()->make(
            ['start_date' => $startDate, 'end_date' => $endDate],
            [
                'start_date' => 'nullable|date|date_format:Y-m-d'.(is_null($endDate) ? '' : '|before:end_date'),
                'end_date'   => 'nullable|date|date_format:Y-m-d'.(is_null($startDate) ? '' : '|after:start_date'),
            ]
        )->errors()->all();

        if ($shouldValidate && ! empty($datesErrors)) {
            return [
                'ok'         => false,
                'errors'     => $datesErrors,
                'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ];
        }

        return [
            'ok'         => true,
            'errors'     => [],
            'statusCode' => Response::HTTP_OK,
        ];
    }

    public function validateDestroyTweets()
    {
        $hasValidDates = $this->hasValidDates();

        if (! $hasValidDates['ok']) {
            $this->ok = $hasValidDates['ok'];
            $this->errors = $hasValidDates['errors'];
            $this->statusCode = $hasValidDates['statusCode'];

            return false;
        }

        if ($this->relatedTask === null) {
            $this->ok = false;
            $this->errors = [__('messages.task_add_target_not_found')];
            $this->statusCode = Response::HTTP_UNAUTHORIZED;

            return false;
        }

        return true;
    }

    protected function hasValidManagedTaskId()
    {
        $lookupType = null;

        if (in_array($this->targetedTask, ['Likes', 'DestroyLikes'])) {
            $lookupType = ManagedDestroyLikesOperation::class;
        } elseif (in_array($this->targetedTask, ['UserTweets', 'DestroyTweets'])) {
            $lookupType = ManagedDestroyTweetsOperation::class;
        }

        $userManagedTasks = Task::where('status', 'queued')
        ->where('type', $lookupType)
        ->whereIn('socialuser_id', $this->user->socialUsers->pluck('id'))
        ->get()->last();

        return ! empty($userManagedTasks);
    }

    public function addTask(SocialUser $socialUser, $operationClassName)
    {
        $settings = ['targeted_task_id' => $this->relatedTask ? $this->relatedTask->id : null, 'settings' => $this->requestData['settings'] ?? null];

        if ($this->managedByTaskId && $this->hasValidManagedTaskId()) {
            $settings['managedByTaskId'] = $this->managedByTaskId;
        }

        $task = Task::create(
            [
                'socialuser_id'      => $socialUser->id,
                'type'               => $operationClassName,
                'status'             => 'queued',
                'extra'              => $settings,
                'managed_by_task_id' => $settings['managedByTaskId'] ?? null,
            ]
        );

        $this->ok = true;
        $this->statusCode = Response::HTTP_OK;
        $this->errors = [];
        $this->data = array_merge($this->data, ['task_id' => $task->id]);
    }

    public function hasPreviousTask(string $operationClassName)
    {
        $oldTasks = Task::whereIn('socialuser_id', $this->user->socialUsers->pluck('id')->toArray())
        ->where('type', $operationClassName)
        ->where('status', 'queued')
        ->get();

        $hasOngoingTask = $oldTasks->count() != 0;

        if ($hasOngoingTask) {
            $this->ok = false;
            $this->data = ['task_id' => $oldTasks->last()->id];
            $this->statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;

            return true;
        }

        return false;
    }

    public function resolveUser($taskScope)
    {
        return UserManager::resolveUser($this->user, $taskScope);
    }

    public function isOk()
    {
        return $this->ok;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getAvailableTasks()
    {
        return array_keys($this->availableTasks);
    }
}

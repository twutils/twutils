<?php

namespace App\TwUtils\TwitterOperations;

use Exception;
use App\Models\Task;
use AppNext\Tasks\Config;
use App\Models\SocialUser;
use Illuminate\Support\Str;
use App\Jobs\CompleteTaskJob;
use App\TwUtils\Services\JobsService;
use Illuminate\Support\Facades\Cache;
use Abraham\TwitterOAuth\TwitterOAuthException;

abstract class TwitterOperation
{
    protected $shortName;

    protected $parameters = [];

    protected $data = [];

    protected $headers = [];

    protected $socialUser;

    protected $endpoint;

    protected $httpMethod;

    protected $task = null;

    protected $response;

    protected $scope;

    protected $doRequestParameters;

    abstract public function dispatch();

    public function initJob()
    {
    }

    final public function doRequest($socialUser, $task, $parameters)
    {
        $this->doRequestParameters = $parameters;

        $this->socialUser = $socialUser;
        $this->task = $task;

        if (! $this->shouldContinueProcessing()) {
            return;
        }

        $this->handleJobParameters($parameters);

        $connector = app(\App\TwUtils\ITwitterConnector::class);
        $twitterClient = $connector->get($socialUser);

        $parameters = $this->getTwitterClientParameters();

        try {
            $this->response = (array) $twitterClient->{Config::getMethod($this::class)}(
                Config::getEndpoint($this::class),
                $parameters
            );

            if (app('env') === 'testing' && app()->has('AfterHTTPRequest')) {
                app()->make('AfterHTTPRequest');
            }
        } catch (Exception $e) {
            try {
                $this->headers = $twitterClient->getLastXHeaders();
                $this->data['nextJobDelay'] = app(JobsService::class)->getNextJobDelayFromHeaders($this->headers);
            } catch (Exception $e) {
            }

            return $this->handleTwitterException($e);
        }

        $this->headers = $twitterClient->getLastXHeaders();
        $this->data['nextJobDelay'] = app(JobsService::class)->getNextJobDelayFromHeaders($this->headers);

        $this->handleResponse();
    }

    protected function saveResponse()
    {
        throw new \Exception('saveResponse function is not implemented');
    }

    protected function shouldBuildNextJob()
    {
        throw new \Exception('shouldBuildNextJob function is not implemented');
    }

    protected function buildNextJob()
    {
        throw new \Exception('buildNextJob function is not implemented');
    }

    protected function shouldContinueProcessing()
    {
        if (
            (is_null($this->task) || is_null($this->task->fresh())) &&
            ! in_array(get_class($this), [FetchUserInfoOperation::class, RevokeAccessOperation::class])
        ) {
            return false;
        }

        return true;
    }

    protected function handleTwitterException($e)
    {
        \Log::warning($e.'');

        if ($e instanceof TwitterOAuthException) {
            if (Cache::get('rebuild_attempts_'.$this->task->id) < config('twutils.exception_rebuild_task_max_attempts')) {
                return $this->rebuildJob();
            } elseif ($this->task) {
                $this->breakTask($this->task, $this->response, $e);
            }
        }
    }

    protected function rebuildJob()
    {
        Cache::increment('rebuild_attempts_'.$this->task->id);

        $dispatchedJob = $this->dispatch();

        if ($dispatchedJob && $this->headers) {
            $dispatchedJob->delay(app(JobsService::class)->getNextJobDelayFromHeaders($this->headers));
        }
    }

    protected function handleResponse()
    {
        if (empty($this->response['errors'])) {
            try {
                $this->saveResponse();
            } catch (\Exception $e) {
                if ($this->task) {
                    $this->breakTask($this->task, $this->response, $e);
                }
            }
            if ($this->shouldBuildNextJob()) {
                $this->buildNextJob();
            }
        } else {
            $this->handleErrorResponse();
        }
    }

    public function socialUserHasPrivilege($socialUser)
    {
        $userScope = $socialUser->scope;

        return collect($this->scope)
        ->every(
            function ($privilege) use ($userScope) {
                return in_array($privilege, $userScope);
            }
        );
    }

    public function setSocialUser(SocialUser $socialUser)
    {
        $this->socialUser = $socialUser;

        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;

        return $this;
    }

    public function getScope()
    {
        return $this->scope;
    }

    protected function setCompletedTask($task)
    {
        dispatch(new CompleteTaskJob($task));

        $this->afterCompletedTask($task);
    }

    protected function afterCompletedTask(Task $task)
    {
    }

    protected function handleJobParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    protected function getTwitterClientParameters()
    {
        return $this->parameters;
    }

    protected function handleErrorResponse()
    {
        if ($this->task) {
            $this->breakTask($this->task, $this->response);
        }
    }

    protected function breakTask($task, $response, $exception = null)
    {
        $breakData = [];

        \Log::info('Task '.$task->id.' was broken. Twitter Response: '.json_encode($response));
        \Log::info('Task '.$task->id.' was broken. Exception: '.($exception ? $exception.'' : ''));

        if (! is_null($exception)) {
            $task->exception = Str::limit($exception->__toString(), 10000);

            $breakData['exceptionClass'] = get_class($exception);
        }

        $task->status = 'broken';
        $task->extra = array_merge($task->extra ?? [], $breakData);
        $task->save();
    }

    public function getValidators(): array
    {
        return [];
    }

    final public function getShortName(): string
    {
        return $this->shortName;
    }
}

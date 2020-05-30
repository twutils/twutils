<?php

namespace App\TwUtils\TwitterOperations;

use Cache;
use App\Task;
use Exception;
use App\SocialUser;
use Illuminate\Support\Str;
use App\TwUtils\JobsManager;
use App\Jobs\CompleteTaskJob;
use Abraham\TwitterOAuth\TwitterOAuthException;
use App\TwUtils\TwitterOperations\FetchUserInfoOperation;

abstract class TwitterOperation
{
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
        if (
            get_class($this) !== FetchUserInfoOperation::class && (is_null($task) || is_null($task->fresh()))
        )
        {
            return ;
        }

        $this->doRequestParameters = $parameters;

        $this->socialUser = $socialUser;
        $this->task = $task;
        $this->handleJobParameters($parameters);

        $connector = app(\App\TwUtils\ITwitterConnector::class);
        $twitterClient = $connector->get($socialUser);

        $parameters = $this->getTwitterClientParameters();

        try {
            $this->response = (array) $twitterClient->{$this->httpMethod}($this->endpoint, $parameters);

            \Log::info([
                'endpoint' => $this->endpoint,
                'parameters' => $parameters,
                'response' => $this->response,
                'headers'=> $twitterClient->getLastXHeaders(),
            ]);

            if (app('env') === 'testing' && app()->has('AfterHTTPRequest')) {
                app()->make('AfterHTTPRequest');
            }
        } catch (Exception $e) {
            try {
                $this->headers = $twitterClient->getLastXHeaders();
                $this->data['nextJobDelay'] = JobsManager::getNextJobDelayFromHeaders($this->headers);
            } catch (Exception $e) {
            }

            return $this->handleTwitterException($e);
        }

        $this->headers = $twitterClient->getLastXHeaders();
        $this->data['nextJobDelay'] = JobsManager::getNextJobDelayFromHeaders($this->headers);

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
            $dispatchedJob->delay(JobsManager::getNextJobDelayFromHeaders($this->headers));
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
            $task->exception = Str::limit($exception->__toString(), 2000);

            $breakData['exceptionClass'] = get_class($exception);
        }

        $task->status = 'broken';
        $task->extra = array_merge($task->extra ?? [], $breakData);
        $task->save();
    }
}

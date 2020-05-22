<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Task;
use App\TwUtils\TasksAdder;
use App\TwUtils\TwitterOperations\destroyLikesOperation;
use App\TwUtils\TwitterOperations\destroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tasks = Task::whereIn('socialuser_id', $request->user()->socialUsers->pluck('id'))
                ->orderByDesc('created_at')
                ->get();

        $tasks = $tasks->map(function ($task) use ($tasks) {
            $taskArray = $task->toArray();

            if ($task->type === ManagedDestroyLikesOperation::class) {
                $managedTask = $tasks->where('managed_by_task_id', $task->id)->where('type', destroyLikesOperation::class)->first();

                $taskArray['removedCount'] = ($managedTask ?? optional())->removedCount;
            }

            if ($task->type === ManagedDestroyTweetsOperation::class) {
                $managedTask = $tasks->where('managed_by_task_id', $task->id)->where('type', destroyTweetsOperation::class)->first();

                $taskArray['removedCount'] = ($managedTask ?? optional())->removedCount;
            }

            return $taskArray;
        });

        return $tasks;
    }

    public function create(Request $request)
    {
        $targetedTask = $request->segment(2);
        $relatedTask = Task::find($request->segment(3)) ?? (Task::find($request->id) ?? null);

        $addTask = new TasksAdder($targetedTask, $request->all(), $relatedTask, auth()->user());

        return response(['ok'=> $addTask->isOk(), 'errors' => $addTask->getErrors(), 'data' => $addTask->getData()], $addTask->getStatusCode());
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        return $task;
    }

    public function getManagedTasks(Request $request, Task $task)
    {
        $this->authorize('seeManagedTasks', $task);

        return $task->managedTasks;
    }

    public function getTaskData(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        if (in_array($task->baseName, Task::TWEETS_LISTS_BASE_NAMES)) {
            return $task->likes()
                    ->paginate(200);
        }

        if (in_array($task->baseName, ['fetchfollowing'])) {
            $perPage = round($task->followings_count / 15);

            return $task
                    ->followings()
                    ->with('tweep')
                    ->paginate($perPage < 500 ? 500 : $perPage);
        }

        if (in_array($task->baseName, ['fetchfollowers'])) {
            $perPage = round($task->followers_count / 15);

            return $task
                    ->followers()
                    ->with('tweep')
                    ->paginate($perPage < 500 ? 500 : $perPage);
        }

        if (in_array($task->baseName, Task::TWEETS_DESTROY_BASE_NAMES)) {
            return Task::find($task->extra['targeted_task_id'])
                ->tweets()
                ->wherePivot('removed', '!=', null)
                ->paginate(200);
        }

        return $task;
    }

    // TODO: test: task is not related to another ongoing task
    // TODO: test: do something to break job operations if the task is ongoing
    public function delete(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        try {
            $task->delete();

            return ['ok' => true];
        } catch (\Exception $e) {
            return ['ok' => false];
        }
    }

    public function listLikesTasks(Request $request)
    {
        return $request->user()->socialUsers[0]
                ->tasks()
                ->where('type', FetchLikesOperation::class)
                ->where('status', 'completed')
                ->get();
    }

    public function listUserTweetsTasks(Request $request)
    {
        return $request->user()->socialUsers[0]
                ->tasks()
                ->where('type', FetchUserTweetsOperation::class)
                ->where('status', 'completed')
                ->get();
    }

    public function edit(Task $task)
    {
    }

    public function update(Request $request, Task $task)
    {
    }

    public function destroy(Task $task)
    {
    }
}

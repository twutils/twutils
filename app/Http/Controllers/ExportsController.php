<?php

namespace App\Http\Controllers;

use App\Exports\TasksExport;
use App\Exports\UsersListTaskExport;
use App\Jobs\CleanExportsDiskJob;
use App\Task;
use App\TwUtils\ExportsManager;
use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;
use File;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class ExportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function download(Request $request, Task $task, string $format = 'excel')
    {
        $this->authorize('view', $task);

        if ($format === 'html') {
            return $this->html($request, $task);
        } else {
            return $this->excel($request, $task);
        }
    }

    protected function html(Request $request, Task $task)
    {
        $isEntitiesTask = in_array($task->type, [FetchEntitiesLikesOperation::class, FetchEntitiesUserTweetsOperation::class]);

        if ($isEntitiesTask && ! empty($taskFiles = Storage::disk(config('filesystems.cloud'))->allFiles($task->id))) {
            $filePath = $taskFiles[0];
            try {
                return redirect()->away(Storage::disk(config('filesystems.cloud'))->temporaryUrl($filePath, now()->addHours(1)));
            } catch (\Exception $e) {
                return Storage::disk(config('filesystems.cloud'))->download($filePath);
            }
        }

        $fileName = "{$task->socialUser->nickname}_{$task->shortName}_{$task->created_at->format('m-d-Y_hia')}.zip";

        $path = ExportsManager::createHtmlZip($task, $fileName);

        return \Storage::disk('htmlTasks')->download($fileName, $fileName);
    }

    protected function excel(Request $request, Task $task)
    {
        $fileName = "{$request->user()->socialUsers[0]->nickname}_{$task->shortName}_{$task->created_at->format('m-d-Y_hia')}.xlsx";

        if (in_array($task->baseName, Task::USERS_LISTS_BASE_NAMES)) {
            return (new UsersListTaskExport($task))->download($fileName, \Maatwebsite\Excel\Excel::XLSX);
        }

        $tweets = collect([]);

        if (in_array($task->baseName, Task::TWEETS_LISTS_BASE_NAMES)) {
            $tweets = $task->likes;
        }

        if (in_array($task->baseName, Task::TWEETS_MANAGED_DESTROY_BASE_NAMES)) {
            $task = Task::where('managed_by_task_id', $task->id)->get()
                ->first(function (Task $task) {
                    return in_array($task->baseName, Task::TWEETS_DESTROY_BASE_NAMES);
                });
        }

        if (in_array($task->baseName, Task::TWEETS_DESTROY_BASE_NAMES)) {
            $tweets = Task::find($task->extra['targeted_task_id'])
                ->tweets()
                ->wherePivot('removed', '!=', null)
                ->get();
        }

        return (new TasksExport($tweets))->download($fileName, \Maatwebsite\Excel\Excel::XLSX);
    }
}

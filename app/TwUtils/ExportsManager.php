<?php

namespace App\TwUtils;

use App\Download;
use App\Task;
use App\Tweep;
use PhpZip\ZipFile;
use Illuminate\Support\Facades\Storage;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;

class ExportsManager
{
    public static function createHtmlZip(Download $download): string
    {
        $zipFile = static::makeTaskZipObject($download->task);

        $fileAbsolutePath = Storage::disk('local')->path($download->id);

        $zipFile
            ->saveAsFile($fileAbsolutePath)
            ->close();

        $zippedStream = fopen($fileAbsolutePath, 'r');

        Storage::disk(config('filesystems.cloud'))->put($download->id, $zippedStream);

        Storage::disk('local')->delete($download->id);

        return $fileAbsolutePath;
    }

    public static function makeTaskZipObject(Task $task): ZipFile
    {
        $zipFile = new \PhpZip\ZipFile();

        $taskId = $task->id;

        $task = $task->load('likes', 'followings', 'followings.tweep', 'followers', 'followers.tweep');

        $taskData = ['tasks' => [$task], 'isLocal' => true];

        if (in_array($task->type, [ManagedDestroyLikesOperation::class, ManagedDestroyTweetsOperation::class])) {
            $taskData['managedTasks'] = Task::with('likes', 'followings', 'followings.tweep', 'followers', 'followers.tweep')
                ->where('managed_by_task_id', $task->id)
                ->get();
        }

        $viewFile = view('task', $taskData)->render();

        $assetsDirectories = ['build_css', 'fonts', 'images', 'js'];

        $publicDisk = Storage::disk('publicRoot');

        foreach ($assetsDirectories as $assetsDirectory) {
            $zipFile->addDirRecursive($publicDisk->path($assetsDirectory), 'assets/'.$assetsDirectory);
        }

        $zipFile
        ->addFromString('index.html', $viewFile);

        $tweeps = $task->getTaskTweeps();
        $availableAvatars = [];

        $tweeps->map(function (Tweep $tweep) use (&$availableAvatars) {
            if (! Storage::disk('public')->exists('avatars/'.$tweep->id_str.'.png')) {
                return;
            }

            $availableAvatars[] = $tweep->id_str;
        });

        foreach ($availableAvatars as $idStr) {
            $zipFile->addFile(Storage::disk('public')->path('avatars/'.$idStr.'.png'), 'avatars/'.$idStr.'.png');
        }

        return $zipFile;
    }
}

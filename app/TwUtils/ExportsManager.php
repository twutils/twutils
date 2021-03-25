<?php

namespace App\TwUtils;

use PhpZip\ZipFile;
use App\Models\Task;
use App\Models\Tweep;
use App\Models\Export;
use Illuminate\Support\Facades\Storage;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;

class ExportsManager
{
    public static function createHtmlZip(Export $export): string
    {
        $zipFile = static::makeTaskZipObject($export);

        $fileAbsolutePath = Storage::disk('local')->path($export->id);

        $zipFile
            ->saveAsFile($fileAbsolutePath)
            ->close();

        $zippedStream = fopen($fileAbsolutePath, 'r');

        Storage::disk(config('filesystems.cloud'))->put($export->id, $zippedStream);

        Storage::disk('local')->delete($export->id);

        return $fileAbsolutePath;
    }

    public static function makeTaskZipObject(Export $export): ZipFile
    {
        $task = $export->task;

        $zipFile = new \PhpZip\ZipFile();

        $taskId = $task->id;

        $task = $task->load('likes', 'followings', 'followings.tweep', 'followers', 'followers.tweep');

        $taskData = ['tasks' => [$task], 'isLocal' => true, 'export' => $export->fresh()->toArray()];

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

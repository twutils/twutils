<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

trait InteractsWithStorage
{
    public static function getStorageDisk(): FilesystemAdapter
    {
        return Storage::disk(static::getStorageDiskName());
    }

    public static function getStorageDiskName(): string
    {
        return config('filesystems.cloud');
    }
}

<?php

namespace AppNext\Tasks;

class Config
{
    public static function getJob(string $taskType): string
    {
        return config("twutils.tasks.{$taskType}.job");
    }
}

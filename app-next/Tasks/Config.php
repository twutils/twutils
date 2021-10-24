<?php

namespace AppNext\Tasks;

class Config
{
    public static function getJob(string $taskType): string
    {
        return config("twutils.tasks.{$taskType}.job");
    }

    public static function getMethod(string $taskType): string
    {
        return config("twutils.tasks.{$taskType}.method");
    }

    public static function getEndpoint(string $taskType): string
    {
        return config("twutils.tasks.{$taskType}.endpoint");
    }
}

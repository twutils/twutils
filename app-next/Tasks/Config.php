<?php

namespace AppNext\Tasks;

class Config
{
    public static function getJob(string $taskTypeClassName): string
    {
        return config("twutils.tasks.{$taskTypeClassName}.job");
    }

    public static function getMethod(string $taskTypeClassName): string
    {
        return config("twutils.tasks.{$taskTypeClassName}.method");
    }

    public static function getEndpoint(string $taskTypeClassName): string
    {
        return config("twutils.tasks.{$taskTypeClassName}.endpoint");
    }

    public static function getShortname(string $taskTypeClassName): string
    {
        return config("twutils.tasks.{$taskTypeClassName}.shortname");
    }

    public static function getUploadPurposes(string $taskTypeClassName): array
    {
        return config("twutils.tasks.{$taskTypeClassName}.upload_purposes");
    }
}

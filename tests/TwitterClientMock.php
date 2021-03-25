<?php

namespace Tests;

class TwitterClientMock
{
    private $twitterResults;

    private $headers;

    private static $lastCallData;

    private static $allCallsData;

    public function __construct($twitterResults, $headers)
    {
        $this->twitterResults = $twitterResults;
        $this->headers = $headers;
    }

    public function getLastXHeaders()
    {
        return $this->headers;
    }

    public function get($endpoint, $parameters)
    {
        if (is_null(static::$allCallsData)) {
            static::$allCallsData = collect();
        }

        static::$lastCallData = compact('endpoint', 'parameters');
        static::$allCallsData->push(static::$lastCallData);

        if ($endpoint === 'friendships/lookup') {
            return $this->followingLookupResult($parameters);
        }

        return $this->twitterResults;
    }

    public function followingLookupResult($parameters)
    {
        $results = [];
        collect(explode(',', $parameters['user_id']))
        ->each(
            function ($id_str) use (&$results) {
                $lookup = collect($this->twitterResults)->where('id_str', $id_str)->first();
                if (! empty($lookup)) {
                    $results[] = $lookup;
                }
            }
        );

        return $results;
    }

    public function post($endpoint, $parameters)
    {
        return $this->get($endpoint, $parameters);
    }

    public static function getLastCallData()
    {
        return static::$lastCallData;
    }

    public static function getAllCallsData()
    {
        return static::$allCallsData;
    }

    public static function clearCallsData()
    {
        static::$allCallsData = collect();
    }
}

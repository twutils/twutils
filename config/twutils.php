<?php

use App\Jobs\DestroyTweetJob;
use App\Jobs\DislikeTweetJob;
use AppNext\Tasks\DestroyLikesByUpload;
use AppNext\Tasks\DestroyTweetsByUpload;
use App\TwUtils\TwitterOperations\DestroyLikesOperation;
use App\TwUtils\TwitterOperations\DestroyTweetsOperation;

return [
    'tasks' => [
        DestroyLikesByUpload::class => [
            'shortname'                => 'ManagedDestroyLikes',
            'scope'                    => 'write',
            'source'                   => 'file',

            'next'                     => true,
            'accepts_uploads_purposes' => [
                'DestroyLikes',
            ],
        ],

        DestroyTweetsByUpload::class => [
            'shortname'                => 'ManagedDestroyTweets',
            'scope'                    => 'write',
            'source'                   => 'file',

            'next'                     => true,
            'accepts_uploads_purposes' => [
                'DestroyTweets',
            ],
        ],

        DestroyLikesOperation::class => [
            'shortname' => 'DestroyLikes',
            'scope'     => 'write',
            'source'    => 'twitter',

            'endpoint'  => 'favorites/destroy',
            'method'    => 'post',
            'job'       => DislikeTweetJob::class,
        ],

        DestroyTweetsOperation::class => [
            'shortname' => 'DestroyTweets',
            'scope'     => 'write',
            'source'    => 'twitter',

            'endpoint'  => 'statuses/destroy',
            'method'    => 'post',
            'job'       => DestroyTweetJob::class,
        ],
    ],

    'twitter_requests_counts' => [
        'fetch_following_lookups' => 100,
        'fetch_likes'             => 200,
    ],
    'database_groups_chunk_counts' => [
        'fetch_following_lookups' => 50,
        'fetch_likes'             => 50,
        'tweep_db_where_in_limit' => 500,
    ],
    'minimum_expected_likes'              => 3,
    'tasks_limit_per_user'                => 30,
    'exception_rebuild_task_max_attempts' => 3,
];

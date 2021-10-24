<?php

use App\Jobs\DestroyTweetJob;
use App\Jobs\DislikeTweetJob;
use AppNext\Tasks\DestroyLikesByUpload;
use AppNext\Tasks\DestroyTweetsByUpload;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\DestroyLikesOperation;
use App\TwUtils\TwitterOperations\RevokeAccessOperation;
use App\TwUtils\TwitterOperations\DestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchUserInfoOperation;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchFollowingLookupsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation;

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

        FetchLikesOperation::class => [
            'shortname' => 'Likes',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'favorites/list',
            'method'    => 'get',
            'job'       => null,
        ],

        FetchUserTweetsOperation::class => [
            'shortname' => 'UserTweets',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'statuses/user_timeline',
            'method'    => 'get',
            'job'       => null,
        ],

        FetchEntitiesLikesOperation::class => [
            'shortname' => 'EntitiesLikes',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'favorites/list',
            'method'    => 'get',
            'job'       => null,
        ],

        FetchEntitiesUserTweetsOperation::class => [
            'shortname' => 'EntitiesUserTweets',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'statuses/user_timeline',
            'method'    => 'get',
            'job'       => null,
        ],

        FetchFollowingOperation::class => [
            'shortname' => 'Following',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'friends/list',
            'method'    => 'get',
            'job'       => null,
        ],

        FetchFollowersOperation::class => [
            'shortname' => 'Followers',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'followers/list',
            'method'    => 'get',
            'job'       => null,
        ],

        FetchFollowingLookupsOperation::class => [
            'shortname' => '...',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'friendships/lookup',
            'method'    => 'get',
            'job'       => null,
        ],

        FetchUserInfoOperation::class => [
            'shortname' => '...',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'users/show',
            'method'    => 'get',
            'job'       => null,
        ],

        RevokeAccessOperation::class => [
            'shortname' => '...',
            'scope'     => 'read',
            'source'    => 'twitter',

            'endpoint'  => 'oauth/invalidate_token',
            'method'    => 'post',
            'job'       => null,
        ],

        ManagedDestroyLikesOperation::class => [
            'shortname' => 'ManagedDestroyLikes',
            'scope'     => 'write',
            'source'    => 'twitter',

            'endpoint'  => '...',
            'method'    => '...',
            'job'       => null,
        ],

        ManagedDestroyTweetsOperation::class => [
            'shortname' => 'ManagedDestroyTweets',
            'scope'     => 'write',
            'source'    => 'twitter',

            'endpoint'  => '...',
            'method'    => '...',
            'job'       => null,
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

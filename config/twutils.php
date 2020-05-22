<?php

return [
    'twitter_requests_counts' => [
        'fetch_following_lookups' => 100,
        'fetch_likes'             => 200,
    ],
    'database_groups_chunk_counts' => [
        'fetch_following_lookups' => 50,
        'fetch_likes'             => 50,
        'tweep_db_where_in_limit' => 999,
    ],
    'minimum_expected_likes'              => 3,
    'tasks_limit_per_user'                => 30,
    'exception_rebuild_task_max_attempts' => 3,
];

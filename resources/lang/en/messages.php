<?php

use App\Models\Export;

return [
    'brand_desc' => 'Twitter Utilities (TwUtils) is a set of utilities for managing your twitter account.',

    'task_add_max_number'          => 'You have reached the maximum number of tasks for your account',
    'task_add_target_not_found'    => 'The targeted source task is not available',
    'task_add_no_privilege'        => 'This task requires authorizing TwUtils for a higher access level to your twitter account',
    'task_add_bad_request'         => 'Failure to determine the task type',
    'task_add_unauthorized_access' => 'You don\'t have access for using this task',

    'task_add_upload_wrong_purpose' => 'Can\'t chose the selected upload for this type of task',

    'socialauth_canceled' => 'We are denied to get the response from twitter.',
    'deleteMe_canceled'   => 'Great! Your account deletion request has been canceled..',
    'deleteMe_pending'    => 'There is a pending request for the deletion of your account, cancel it first.',

    'twitter_account'     => 'Twitter Account',
    'twitter_connections' => 'Twitter Access',

    'danger_zone'        => 'Danger Zone',
    'deleteMe'           => 'Remove my account',
    'deleteMe_desc'      => 'Remove my account (on TwUtils, not on Twitter) and all the tasks associated with it. This action cannot be undone.',
    'deleteMe_guide'     => 'Specify when you want your account to be deleted, leave it blank if you want to remove it immediately.',
    'accountToBeRemoved' => 'Your account will be removed',

    'privilege'     => 'Privilege',
    'read'          => 'Read',
    'write'         => 'Write',
    'add'           => 'Add',
    'revoke_access' => 'Revoke Access',
    'activity'      => 'Activity',
    'registered'    => 'Registered',
    'last_login'    => 'Last Login',

    'home'       => 'Home',
    'profile'    => 'Profile',
    'twitter'    => 'Twitter',
    'login'      => 'Login',
    'login_with' => 'Login with',
    'will_start' => 'will start on',
    'seconds'    => 'Seconds',
    'logout'     => 'Logout',

    'page' => 'Page',

    'explore'   => 'Explore',
    'dashboard' => 'Dashboard',

    'startTask' => 'Start..',

    'confirmDeleteMe'     => 'Are you sure you want to delete your account? This action cannot be undone',
    'confirmRemoveExport' => 'Are you sure you want to remove this export?',

    'goto_home' => 'Start using it..',
    'features'  => 'Features',

    'call_to_action_desc' => 'Login with twitter to check out the features that you can use.',
    'task'                => 'Task',
    'tasks'               => 'Tasks',
    'status'              => 'Status',
    'created_at'          => 'Created At',
    'updated_at'          => 'Updated At',
    'details'             => 'Details',
    'no_tasks'            => 'No tasks yet..',

    'backup'    => 'Backup',
    'remove'    => 'Remove',
    'cancel'    => 'Cancel',
    'select'    => 'Select',
    'selected'  => 'Selected',

    'download_likes'      => 'Download Likes',
    'download_likes_desc' => 'Download an excel sheet version of your twitter likes',

    'likes'                      => 'Likes',
    'backup_likes'               => 'Backup My Likes',
    'backup_likes_desc'          => 'Fetch a copy of your current likes.',
    'backup_likes_entities'      => 'Backup Likes (With Media)',
    'backup_likes_entities_desc' => 'Fetch a copy of your current likes (With Media)',
    'user_tweets'                => 'Backup My Tweets',
    'user_tweets_desc'           => 'Fetch a copy of your current tweets.',
    'destroy_likes'              => 'Remove My Likes',
    'destroy_likes_desc'         => '',
    'destroy_tweets'             => 'Remove My Tweets',
    'destroy_tweets_desc'        => '',

    'drop_hint' => [
        'destroyTweets' => 'Drop tweet.js file located in your twitter archive file.',
        'destroyLikes'  => 'Drop like.js file located in your twitter archive file.',
    ],

    'removedLikes'  => 'Removed Likes',
    'removedTweets' => 'Removed Tweets',

    'initial'                                                              => 'Initial',
    'started'                                                              => 'Started',
    'success'                                                              => 'Success',

    'download'                                                             => 'Download',
    'completed'                                                            => 'Completed',
    'staging'                                                              => 'Staging',
    'queued'                                                               => 'Queued',
    'broken'                                                               => 'Broken',
    \App\TwUtils\TwitterOperations\FetchLikesOperation::class              => 'Backup Likes',
    \App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation::class      => 'Backup Likes (With Media)',
    \App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation::class => 'Backup Tweets (With Media)',
    \App\TwUtils\TwitterOperations\FetchUserTweetsOperation::class         => 'Backup Tweets',
    \App\TwUtils\TwitterOperations\FetchFollowingOperation::class          => 'Fetch Following',
    \App\TwUtils\TwitterOperations\FetchFollowersOperation::class          => 'Fetch Followers',
    \App\TwUtils\TwitterOperations\DestroyLikesOperation::class            => 'Remove Likes',
    \App\TwUtils\TwitterOperations\DestroyTweetsOperation::class           => 'Remove Tweets',
    \App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation::class     => 'Remove Likes',
    \App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation::class    => 'Remove Tweets',

    'fetch_following'      => 'Backup My Following',
    'fetch_following_desc' => 'Fetch a copy of your twitter following list.',

    'following' => 'Following',

    'fetch_followers'      => 'Backup My Followers',
    'fetch_followers_desc' => 'Fetch a copy of your twitter followers list.',

    'followers' => 'Followers',

    'tweet'  => 'Tweet',
    'tweets' => 'Tweets',

    'chose'                => 'Chose',
    'adding_backup_likes'  => 'Adding the Backup Likes task.. waiting for response.',
    'ongoing_backup_likes' => 'There is ongoing task already for backing up your likes, please wait until it\'s completed..',

    'adding_user_tweets'  => 'Adding the Backup Tweets task.. waiting for response.',
    'ongoing_user_tweets' => 'There is ongoing task already for backing up your tweets, please wait until it\'s completed..',

    'adding_fetch_following'  => 'Adding the fetch following task.. waiting for response',
    'ongoing_fetch_following' => 'There is ongoing task already for fetching your following, please wait until it\'s completed..',

    'adding_fetch_followers'  => 'Adding the fetch followers task.. waiting for response',
    'ongoing_fetch_followers' => 'There is ongoing task already for fetching your followers, please wait until it\'s completed..',

    'loading_destroy_likes'  => 'Destroy Likes, waiting for response',
    'ongoing_destroy_likes'  => 'There is ongoing task already for cleaning your likes, please wait until it\'s completed..',
    'loading_destroy_tweets' => 'Destroy Tweets, waiting for response',
    'ongoing_destroy_tweets' => 'There is ongoing task already for cleaning your Tweets, please wait until it\'s completed..',

    'create' => 'Create',
    'start'  => 'Start',
    'close'  => 'Close',

    'name'    => 'Name',
    'email'   => 'Email',
    'purpose' => 'Purpose',
    'message' => 'Message',
    'send'    => 'Send',

    'contact_us'                     => 'Contact Us',
    'contact_us_desc'                => 'We would love to hear from you !',
    'contact_us_additional_channels' => 'Also, you can reach out using the following channels:',

    'suggestion'     => 'Suggestion',
    'feedback'       => 'Feedback',
    'ux_improvement' => 'UX Improvement',
    'report_a_bug'   => 'Report a Bug',
    'support'        => 'Support',
    'other'          => 'Other',

    'go_to_details'     => 'Go to details',
    'no_previous_tasks' => 'No Previous Tasks',
    'history'           => 'History',
    'options'           => 'Options',
    'with_media'        => 'With Media',

    'with_media_task_description'   => 'Export tweets attachments as well “Html & Media” after completion',
    'you_can_do_this_later'         => 'You can do this later',

    'total_users'    => 'Total Users',
    'total_tweets'   => 'Total Tweets',
    'sorted_by'      => 'Sorted By',
    'search_results' => 'Search Results',
    'per_page'       => 'Per Page',
    'search'         => 'Search',
    'ascending'      => 'Ascending',
    'descending'     => 'Descending',

    'search_in_users_list'  => 'Search in Handler + Name + Bio',

    'filter_by_media' => 'Filter by Media Type',

    'exploringـmode'             => 'I\'m only exploring and I will not really use it',

    'destroy_tweets_options' => [
        'dates_range'       => 'Dates Range',
        'dates_range_desc'  => 'Using this option you can specify the dates range for the tweets to be deleted.',
        'dates_range_note'  => 'Leaving this empty will remove everything from the beginning until the end.',

        'tweets_source'         => 'Tweets Source',
    ],

    'destroy_tweets_confirmation'   => 'By Confirming, you will remove the tweets from your twitter account. This action can\'t be undone.',
    'destroy_likes_confirmation'    => 'By Confirming, you will remove the likes from your twitter account (according to the range/scope defined). This action can\'t be undone.',

    'destroy_tweets_confirmation_button' => 'Remove My Tweets',
    'destroy_likes_confirmation_button'  => 'Remove My Likes',

    'selected_tweets_source'      => 'Selected Tweets Source',
    'selected_tweets_source_desc' => 'In order to delete the tweets, we need to copy it\'s current state from your account before. To do so, the system created a new copying/backup task, so it can be used later as the source of deletion.',
    'start_date'                  => 'Start Date',
    'end_date'                    => 'End Date',
    'removed'                     => 'Removed',
    'removing'                    => 'Removing',

    'exports'   => [
        Export::TYPE_EXCEL         => 'Excel',
        Export::TYPE_HTML          => 'HTML',
        Export::TYPE_HTMLENTITIES  => 'HTML & Media',
    ],
    'exports_desc'   => [
        Export::TYPE_EXCEL         => 'Excel File',
        Export::TYPE_HTML          => 'Zipped HTML',
        Export::TYPE_HTMLENTITIES  => 'Zipped HTML with Media',
    ],
];

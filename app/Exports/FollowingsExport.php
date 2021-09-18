<?php

namespace App\Exports;

use App\Models\Following;

class FollowingsExport extends UsersListTaskExport
{
    protected function getRelationColumn(): string
    {
        return 'follows_you';
    }

    public function collection()
    {
        return $this->task->load(['followings.tweep'])->followings->reverse()->values()->map(
            function (Following $followoingPivot, $key) {
                $tweep = $followoingPivot->tweep;

                static::$tweepsUrls[$tweep->display_url] = $tweep->url;

                return [
                    'order'                   => $key + 1,
                    'username'                => $this->formatText($tweep->screen_name),
                    'name'                    => $this->formatText($tweep->name),

                    'user_following'          => $tweep->friends_count,
                    'user_followers'          => $tweep->followers_count,
                    'user_tweets'             => $tweep->statuses_count,
                    'user_likes'              => $tweep->favourites_count,

                    'bio'                     => $this->formatText($tweep->description),
                    'user_url'                => $this->formatText($tweep->display_url),
                    'user_location'           => $this->formatText($tweep->location),
                    'user_is_verified'        => $tweep->verified ? 'Yes' : 'No',

                    'follows_you'             => $followoingPivot->followed_by ? 'Yes' : 'No',

                    'user_joined_twitter_at'  => $tweep->tweep_created_at,
                    'id'                      => $tweep->id_str,
                    'permalink'               => 'https://twitter.com/intent/user?user_id='.$tweep->id_str,
                ];
            }
        );
    }
}

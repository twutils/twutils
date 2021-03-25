<?php

namespace App\TwUtils;

use Carbon\Carbon;
use App\Models\Tweep;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class TweepsManager
{
    public static function insertOrUpdateMultipleTweeps(Collection $tweeps)
    {
        $tweeps = $tweeps->unique('id_str')->map(function ($user) {
            return static::mapResponseUserToTweep((array) $user);
        });

        $foundTweeps = Tweep::whereIn('id_str', $tweeps->pluck('id_str'))->get();
        $foundTweepsIds = $foundTweeps->pluck('id_str');

        $notFound = $tweeps->pluck('id_str')->diff($foundTweepsIds);

        $foundTweeps->map(function (Tweep $tweep) use ($tweeps) {
            return static::updateTweepIfNeeded($tweep, $tweeps->where('id_str', $tweep->id_str)->first());
        });

        $notFound->map(function ($tweepIdStr) use ($tweeps) {
            return static::createTweep($tweeps->where('id_str', $tweepIdStr)->first());
        });
    }

    public static function createOrFindFromFollowing(array $user)
    {
        $tweep = Tweep::where('id_str', $user['id_str'])->first();

        $mappedTweet = static::mapResponseUserToTweep($user);

        if (is_null($tweep)) {
            $tweep = static::createTweep($mappedTweet);
        } else {
            $tweep = static::updateTweepIfNeeded($tweep, $mappedTweet);
        }

        return $tweep;
    }

    public static function updateTweepIfNeeded($tweep, $mappedTweep)
    {
        $needUpdate = false;

        foreach ($mappedTweep as $key => $value) {
            if ($tweep->$key === $value) {
                continue;
            }
            $needUpdate = true;
            break;
        }

        if ($needUpdate) {
            $tweep->update($mappedTweep);
        }

        return $tweep;
    }

    public static function createTweep(array $tweep)
    {
        return Tweep::create($tweep);
    }

    public static function mapResponseUserToTweep(array $user): array
    {
        $displayUrl = null;
        if (! empty($user['entities']->url) && ! empty($user['entities']->url->urls)) {
            $displayUrl = $user['entities']->url->urls[0]->display_url ?? null;
        }

        return [
            'id_str'           => $user['id_str'],
            'name'             => $user['name'],
            'avatar'           => $user['profile_image_url_https'],
            'screen_name'      => $user['screen_name'],
            'location'         => $user['location'] ?? null,
            'description'      => Str::limit($user['description'], 251) ?? null,
            'url'              => $user['url'] ?? null,
            'display_url'      => $displayUrl ?? null,
            'followers_count'  => $user['followers_count'] ?? null,
            'friends_count'    => $user['friends_count'] ?? null,
            'favourites_count' => $user['favourites_count'] ?? null,
            'verified'         => $user['verified'] ?? null,
            'protected'        => $user['protected'] ?? null,
            'statuses_count'   => $user['statuses_count'] ?? null,
            'background_color' => $user['profile_background_color'] ?? null,
            'background_image' => $user['profile_banner_url'] ?? null,
            'tweep_created_at' => Carbon::createFromTimestamp(strtotime($user['created_at'] ?? 1)),
            'created_at'       => now(),
            'updated_at'       => now(),
        ];
    }
}

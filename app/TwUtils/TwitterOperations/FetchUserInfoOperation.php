<?php

namespace App\TwUtils\TwitterOperations;

use App\Models\Task;
use App\TwUtils\AssetsManager;

class FetchUserInfoOperation extends TwitterOperation
{
    protected $endpoint = 'users/show';

    protected $scope = 'read';

    protected $httpMethod = 'get';

    protected function shouldBuildNextJob()
    {
        return false;
    }

    protected function buildNextJob()
    {
    }

    protected function handleTwitterException($e)
    {
    }

    protected function afterCompletedTask(Task $task)
    {
    }

    protected function saveResponse()
    {
        $socialUser = $this->socialUser;

        $response = $this->response;
        $displayUrl = null;

        if (! empty($response['entities']->url) && ! empty($response['entities']->url->urls)) {
            $displayUrl = $response['entities']->url->urls[0]->display_url;
        }

        $socialUser->location = $response['location'] ?? null;
        $socialUser->description = $response['description'] ?? null;
        $socialUser->url = $response['url'] ?? null;
        $socialUser->display_url = $displayUrl ?? null;
        $socialUser->followers_count = $response['followers_count'] ?? null;
        $socialUser->friends_count = $response['friends_count'] ?? null;
        $socialUser->favourites_count = $response['favourites_count'] ?? null;
        $socialUser->statuses_count = $response['statuses_count'] ?? null;
        $socialUser->background_color = $response['profile_background_color'] ?? null;
        $socialUser->background_image = $response['profile_banner_url'] ?? null;

        $socialUserAvatar = $response['profile_image_url_https'] ?? null;

        try {
            $socialUser->background_image = app(AssetsManager::class)->storeImage($socialUser->background_image.'/1500x500', $socialUser->social_user_id.'_bg.png', 600);
        } catch (\Exception $e) {
        }

        try {
            if (! is_null($socialUserAvatar)) {
                $socialUser->avatar = app(AssetsManager::class)->storeAvatar($socialUserAvatar, $socialUser->social_user_id);
            }
        } catch (\Exception $e) {
        }

        $socialUser->save();

        $socialUser->touch();
    }

    protected function buildParameters()
    {
        return [
            'user_id'          => $this->socialUser->social_user_id,
            'include_entities' => true,
        ];
    }

    public function dispatch()
    {
        $parameters = $this->buildParameters();

        $this->doRequest($this->socialUser, $this->task, $parameters);
    }
}

<?php

namespace App\TwUtils\TwitterOperations;

use App\Task;
use App\Media;
use App\Tweet;
use App\Export;
use App\TwUtils\AssetsManager;
use App\Jobs\StartExportMediaJob;
use App\Jobs\FetchEntitiesLikesJob;

class FetchEntitiesLikesOperation extends FetchLikesOperation
{
    protected $shortName = 'EntitiesLikes';
    protected $downloadTweetsWithMedia = true;

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];

        $last = (array) collect($this->response)->last();

        $parameters = $this->buildParameters();

        $parameters['max_id'] = $last['id_str'];

        dispatch(new FetchEntitiesLikesJob($parameters, $this->socialUser, $this->task))->delay($nextJobDelay);
    }

    public function dispatch()
    {
        $parameters = $this->buildParameters();

        try {
            return dispatch(new FetchEntitiesLikesJob($parameters, $this->socialUser, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }
}

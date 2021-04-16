<?php

namespace App\Jobs;

use App\Models\Tweep;
use App\TwUtils\Base\Job;
use App\TwUtils\Services\AssetsService;
use GuzzleHttp\Exception\ClientException;

class SaveTweepAvatarJob extends Job
{
    protected $tweepIdStr;

    protected $task;

    protected AssetsService $assetsManager;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $tweepIdStr)
    {
        $this->queue = 'avatars';
        $this->tweepIdStr = $tweepIdStr;

        $this->assetsManager = app(AssetsService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tweep = Tweep::where('id_str', $this->tweepIdStr)->get()->last();

        try {
            $response = app('HttpClient')->get($tweep->avatar);
        } catch (ClientException $e) {
            return;
        }

        $base64Body = base64_encode((string) $response->getBody());

        $this->assetsManager->storeImage($base64Body, $tweep->id_str.'.png', 80, 80, 'avatars');
    }
}

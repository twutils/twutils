<?php

namespace App\Jobs;

use App\Jobs\ZipEntitiesJob;
use App\Task;
use App\Tweep;
use App\TwUtils\AssetsManager;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveTweepAvatarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $tweepIdStr;
    protected $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $tweepIdStr)
    {
        $this->queue = 'avatars';
        $this->tweepIdStr = $tweepIdStr;
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

        AssetsManager::storeImage($base64Body, $tweep->id_str.'.png', 80, 80, 'avatars');
    }
}

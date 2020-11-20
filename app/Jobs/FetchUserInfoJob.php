<?php

namespace App\Jobs;

use App\SocialUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TwUtils\TwitterOperations\FetchUserInfoOperation;

class FetchUserInfoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $socialUser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SocialUser $socialUser)
    {
        $this->socialUser = $socialUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fetchUserInfoOperation = new FetchUserInfoOperation();

        $fetchUserInfoOperation->setSocialUser($this->socialUser)->dispatch();
    }
}

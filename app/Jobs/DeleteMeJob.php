<?php

namespace App\Jobs;


use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\TwUtils\Base\Job;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteMeJob extends Job
{
    private $user;



    public function __construct($user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $user = $this->user->fresh();

        if (is_null($user)) {
            return;
        }

        $removeDate = $user->remove_at;

        if ($removeDate !== null && now()->greaterThanOrEqualTo($removeDate)) {
            $user->delete();
        }
    }
}

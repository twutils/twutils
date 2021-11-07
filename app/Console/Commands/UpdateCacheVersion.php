<?php

namespace App\Console\Commands;

use Sentry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UpdateCacheVersion extends Command
{
    protected $signature = 'update-cache-version';

    protected $description = 'Update cache version';

    public function handle()
    {
        try {
            $this->updateCacheVersion();
        } catch (\Exception $e) {
            Sentry::captureException($e);

            if (app('env') !== 'production') {
                throw $e;
            }
        }
    }

    protected function updateCacheVersion()
    {
        $response = Http::withHeaders([
            'Authorization' => 'token '.env('GITHUB_TOKEN'),
        ])->get('https://api.github.com/repos/MohannadNaj/twutils/releases/latest');

        $jsonResponse = $response->json();

        if (empty($versionFound = $jsonResponse['name'] ?? null)) {
            throw new \Exception("Couldn't find version. GitHub Response: \n".$response->body(), 1);
        }

        Cache::set('app.version', $versionFound);
    }
}

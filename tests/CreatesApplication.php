<?php

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        Hash::driver('bcrypt')->setRounds(4);

        $app->bind('image', ImageMock::class);
        $app->bind('HttpClient', HttpClientMock::class);

        \Storage::fake('local');
        \Storage::fake('tasks');
        \Storage::fake('public');
        \Storage::fake('htmlTasks');
        \Storage::fake('temporaryTasks');
        \Storage::fake(config('filesystems.cloud'));

        return $app;
    }
}

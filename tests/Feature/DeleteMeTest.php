<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Models\Tweep;
use App\Models\Tweet;
use App\Models\Following;
use Carbon\Carbon;
use App\Models\SocialUser;
use App\Jobs\DeleteMeJob;
use App\Jobs\FetchLikesJob;
use Tests\IntegrationTestCase;
use App\Jobs\FetchFollowersJob;
use App\Jobs\FetchFollowingJob;
use App\Jobs\FetchUserTweetsJob;
use App\Jobs\FetchFollowingLookupsJob;

class DeleteMeTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutJobs();
        // set locale
        app()->setLocale('en');
        $this->logInSocialUser('web', ['lastlogin_at' => now()]);
    }

    public function test_user_without_tasks_can_remove_account()
    {
        // navigate
        $response = $this->get('/profile');
        $response->assertStatus(200);
        $this->assertStringContainsString('Remove my account', $response->getContent());

        $response = $this->post('/deleteMe', [
            'day'    => '',
            'hour'   => '',
            'minute' => '',
        ]);

        $response->assertStatus(302);
        $this->fireJobsAndBindTwitter();
        $this->assertEquals(0, User::all()->count());
        $this->assertEquals(0, SocialUser::all()->count());
    }

    public function test_user_can_set_when_to_delete_me_to_zero_seconds_from_now()
    {
        $response = $this->post('/deleteMe', [
            'day'    => '',
            'hour'   => '',
            'minute' => '',
        ]);

        $now = now();

        $response->assertStatus(302);

        // user is not removed yet
        $this->assertEquals(1, User::all()->count());

        // The right job is queued
        $this->assertEquals(DeleteMeJob::class, get_class($this->dispatchedJobs[0]));
        // job is queued with Zero Seconds from now
        $this->assertEquals(0, $this->dispatchedJobs[0]->delay->diffInSeconds($now));

        // Fire jobs
        $this->fireJobsAndBindTwitter();

        // user should be removed now..
        $this->assertEquals(0, User::all()->count());
    }

    public function test_user_can_set_delete_me_to10_minutes_from_now()
    {
        $response = $this->post('/deleteMe', [
            'day'    => '',
            'hour'   => '',
            'minute' => '10',
        ]);

        $now = now();

        $response->assertStatus(302);

        // user is not removed yet
        $this->assertEquals(1, User::all()->count());

        // The right job is queued
        $this->assertEquals(DeleteMeJob::class, get_class($this->dispatchedJobs[0]));
        // job is queued with 10 minutes from now (or less if this test was on a slow process)
        $this->assertLessThanOrEqual(10, $this->dispatchedJobs[0]->delay->diffInMinutes($now));

        Carbon::setTestNow(now()->addMinutes(10));
        // Fire jobs
        $this->fireJobsAndBindTwitter();

        // user should be removed now..
        $this->assertEquals(0, User::all()->count());
    }

    public function test_user_with_multiple_tasks_can_remove_account()
    {
        $this->actingAs(auth()->user(), 'api');
        // task 1
        $this->getJson('/api/following')
        ->assertStatus(200);

        // task 2
        $this->getJson('/api/followers')
        ->assertStatus(200);

        // task 3
        $this->getJson('/api/userTweets')
        ->assertStatus(200);

        // task 4
        $this->getJson('/api/likes')
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([
            // task 1
            [
                'type'        => FetchFollowingJob::class,
                'twitterData' => $this->fetchFollowingResponse(2),
            ],
            // task 2
            [
                'type'        => FetchFollowersJob::class,
                'twitterData' => $this->fetchFollowingResponse(2),
            ],
            // task 3
            [
                'type'        => FetchUserTweetsJob::class,
                'twitterData' => $this->generateUniqueTweets(2),
            ],
            // task 4
            [
                'type'        => FetchLikesJob::class,
                'twitterData' => $this->generateUniqueTweets(2),
            ],
            [
                'type'        => FetchFollowingLookupsJob::class,
                'twitterData' => [],
            ],
        ]);

        $this->assertEquals(1, User::all()->count());
        $this->assertEquals(1, SocialUser::all()->count());
        $this->assertEquals(4, Task::all()->count());

        $this->assertEquals(2, Following::all()->count());
        $this->assertEquals(4, Tweet::all()->count());

        $this->assertEquals(2, Task::find(1)->followings->count());
        $this->assertEquals(2, Task::find(2)->followers->count());
        $this->assertEquals(2, Task::find(3)->tweets->count());
        $this->assertEquals(2, Task::find(4)->tweets->count());

        $this->actingAs(auth()->user(), 'web');

        $lastJobIndex = count($this->dispatchedJobs);

        $response = $this->post('/deleteMe', [
            'day'    => '',
            'hour'   => '',
            'minute' => '',
        ]);

        $this->fireJobsAndBindTwitter([], $lastJobIndex);

        $this->assertEquals(0, User::all()->count());
        $this->assertEquals(0, SocialUser::all()->count());
        $this->assertEquals(0, Task::all()->count());
        $this->assertEquals(0, Following::all()->count());
        $this->assertEquals(0, Tweet::all()->count());
        $this->assertEquals(0, Tweep::all()->count());
    }
}

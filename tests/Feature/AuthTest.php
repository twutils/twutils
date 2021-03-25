<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SocialUser;
use App\Jobs\FetchUserInfoJob;
use Tests\IntegrationTestCase;

class AuthTest extends IntegrationTestCase
{
    public function test_welcome_page_exists()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_home_page_exists()
    {
        $response = $this->get('/app');
        $response->assertStatus(302);
    }

    public function test_home_page_exists_for_logged_in_users()
    {
        $this->logInSocialUser('web');

        $response = $this->get('/app');
        $response->assertStatus(200);
    }

    public function test_user_profile_is_up_to_date_each_15_minute()
    {
        $this->logInSocialUser('web');
        $this->withoutJobs();

        $this->assertNotEquals('204 No Content', auth()->user()->socialUser->description);

        // User visits dashboard page with outdated profile
        $response = $this->get('/app');
        $response->assertStatus(200);

        // A job will be dispatched to update user's profile
        $this->assertCountDispatchedJobs(1, FetchUserInfoJob::class);

        $lastJobIndex = count($this->dispatchedJobs);

        // Twitter will return the stub response '/tests/_stubs/user_info_response.json' but with "Old Bio"
        $this->fireJobsAndBindTwitter([
            [
                'type'        => FetchUserInfoJob::class,
                'twitterData' => ['description' => 'Old Bio'] + ((array) $this->getStub('user_info_response.json')),
            ],
        ]);

        // The "Old Bio" is saved
        $this->assertEquals('Old Bio', auth()->user()->socialUser->fresh()->description);

        // Set user's profile last update to: before 14 minutes
        tap(auth()->user()->fresh()->socialUser, function (SocialUser $socialUser) {
            $socialUser->updated_at = now()->subMinutes(14);
            $socialUser->save();
        });

        // User visits dashboard page with 14-min profile age
        $response = $this->get('/app');
        $response->assertStatus(200);

        // No job should be queued to update the profile
        $this->assertCountDispatchedJobs(1, FetchUserInfoJob::class);
        // Then...
        // Set user's profile last update to: before 16 minutes
        tap(auth()->user()->socialUser, function (SocialUser $socialUser) {
            $socialUser->updated_at = now()->subMinutes(16);
            $socialUser->save();
        });

        // User visits dashboard page with 16-min profile age
        $response = $this->get('/app');
        $response->assertStatus(200);

        // Since it's more than 15 mins, a job should be dispatched to update the profile
        $this->assertCountDispatchedJobs(2, FetchUserInfoJob::class);

        $this->fireJobsAndBindTwitter([
            [
                'type'        => FetchUserInfoJob::class,
                'twitterData' => ((array) $this->getStub('user_info_response.json')),
            ],
        ], $lastJobIndex);

        $this->assertEquals('204 No Content', auth()->user()->socialUser->fresh()->description);
    }

    public function test_login_page_exists()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_about_page_exists()
    {
        $response = $this->get('/about');
        $response->assertStatus(200);
        $this->get('/ar');
        $this->assertEquals('ar', app()->getLocale());
        $response = $this->get('/about');
        $response->assertStatus(200);
    }

    public function test_contact_page_exists()
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $this->get('/ar');
        $this->assertEquals('ar', app()->getLocale());
        $response = $this->get('/contact');
        $response->assertStatus(200);
    }

    public function test_privacy_page_exists()
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200);
        $this->get('/ar');
        $this->assertEquals('ar', app()->getLocale());
        $response = $this->get('/privacy');
        $response->assertStatus(200);
    }

    public function test_user_can_see_profile_data()
    {
        // set locale
        app()->setLocale('en');
        // register
        $this->logInSocialUser('web', ['lastlogin_at' => now()]);
        // navigate
        $response = $this->get('/profile');
        $response->assertStatus(200);
    }

    public function test_login_redirect_authintacted_user()
    {
        $appUser = User::factory()->create();
        $this->actingAs($appUser);

        $response = $this->get(route('login'));
        $response->assertStatus(302);
    }
}

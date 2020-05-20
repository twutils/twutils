<?php

namespace Tests\Feature;

use App\User;
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
        $appUser = factory(User::class)->create();
        $this->actingAs($appUser);

        $response = $this->get(route('login'));
        $response->assertStatus(302);
    }
}

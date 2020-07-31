<?php

namespace Tests\Feature;

use Mockery;
use App\User;
use Socialite;
use App\SocialUser;
use Illuminate\Support\Str;
use Tests\IntegrationTestCase;
use Illuminate\Support\Facades\Auth;

class TwitterLoginTest extends IntegrationTestCase
{
    public function test_twitter_callback()
    {
        $response = $this->getTwitterResponse();
        $content = $response->getContent();

        $response->assertStatus(302);
    }

    public function test_login_after_logout_doesnot_create_new_user()
    {
        $this->getTwitterResponse();

        $this->assertNotNull(Auth::user());

        Auth::logout();

        $this->getTwitterResponse();

        $this->assertEquals(1, User::all()->count());
    }

    public function test_different_ids_but_duplicate_username_add_new_user()
    {
        $this->getTwitterResponse();

        $username = Auth::user()->username;

        Auth::logout();

        $this->getTwitterResponse(
            'twitter.callback',
            [
                'getId'       => 123456,
                'getNickname' => $username,
            ]
        );

        $this->assertEquals(2, User::all()->count());
        $this->assertEquals(2, Auth::id());
    }

    public function test_multiple_users_logins()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->getTwitterResponse(
                'twitter.callback',
                [
                    'getId'       => $i,
                    'getNickname' => $i,
                ]
            );
            Auth::logout();
        }

        $this->assertEquals(5, SocialUser::all()->count());
        $this->assertEquals(5, User::all()->count());
    }

    public function test_multiple_users_same_username_logins()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->getTwitterResponse(
                'twitter.callback',
                [
                    'getId'       => $i,
                    'getNickname' => 'aaa',
                ]
            );
            Auth::logout();
        }

        $this->assertEquals('aaa,aaa_1,aaa_2,aaa_3,aaa_4', User::all()->pluck('username')->implode(','));
        $this->assertEquals(5, SocialUser::all()->count());
        $this->assertEquals(5, User::all()->count());
    }

    public function test_different_ids_but_duplicate_emails_add_new_user()
    {
        $this->getTwitterResponse();

        $email = Auth::user()->email;

        Auth::logout();

        $this->getTwitterResponse(
            'twitter.callback',
            [
                'getId'    => 123456,
                'getEmail' => $email,
            ]
        );

        $this->assertEquals(2, User::all()->count());
        $this->assertEquals(2, Auth::id());

        $this->assertEquals($email, User::find(1)->email);
        $this->assertEquals($email, User::find(2)->email);

        $this->assertEquals($email, SocialUser::find(1)->email);
        $this->assertEquals($email, SocialUser::find(2)->email);
    }

    public function test_update_email_if_changed_on_next_login()
    {
        $this->getTwitterResponse();

        $email = Auth::user()->email;

        Auth::logout();

        $newEmail = $email.Str::random();

        $this->getTwitterResponse(
            'twitter.callback',
            [
                'getEmail' => $newEmail,
            ]
        );

        $this->assertEquals(1, User::all()->count());
        $this->assertEquals(1, SocialUser::all()->count());
        $this->assertEquals(1, Auth::id());

        $this->assertEquals($newEmail, User::find(1)->email);
        $this->assertEquals($newEmail, SocialUser::find(1)->email);
    }

    public function test_twitter_read_write_callback()
    {
        $response = $this->getTwitterResponse('twitter.rw.callback');

        $response->assertStatus(302);
    }

    public function test_twitter_preserve_read_write_after_read_login_attempt()
    {
        $response = $this->getTwitterResponse('twitter.rw.callback');
        $response->assertStatus(302);

        $userId = auth()->id();

        $response = $this->getTwitterResponse('twitter.callback');
        $response->assertStatus(302);

        $this->getTwitterResponse('twitter.callback');
        $response->assertStatus(302);

        $this->assertEquals(2, SocialUser::all()->count());
        $this->assertEquals(['read', 'write'], SocialUser::find(1)->scope);
        $this->assertEquals(['read'], SocialUser::find(2)->scope);
        $this->assertEquals($userId, SocialUser::find(1)->user_id);
        $this->assertEquals($userId, SocialUser::find(2)->user_id);
    }

    public function test_twitter_add_read_token_after_revoking_it_while_having_read_write_token()
    {
        $response = $this->getTwitterResponse('twitter.callback');
        $response->assertStatus(302);

        $response = $this->getTwitterResponse('twitter.rw.callback');
        $response->assertStatus(302);

        $this->post('revokeSocialUser/'.auth()->user()->socialUsers[0]->id);

        $this->assertEmpty('', auth()->user()->socialUsers[0]->fresh()->token);

        $response = $this->getTwitterResponse('twitter.callback');
        $response->assertStatus(302);

        $this->assertNotEmpty(auth()->user()->socialUsers[0]->fresh()->token);
    }

    public function test_twitter_creates_users()
    {
        $response = $this->getTwitterResponse();
        $this->assertEquals(User::all()->count(), 1);
        $this->assertEquals(SocialUser::all()->count(), 1);
    }

    public function test_twitter_signing_in_user()
    {
        $response = $this->getTwitterResponse();

        $this->assertEquals(Auth::user()->id, User::all()[0]->id, 'User is logged in after social authentication');
    }

    public function test_twitter_read_write_signing_in_user()
    {
        $response = $this->getTwitterResponse('twitter.rw.callback');

        $this->assertEquals(Auth::user()->id, User::all()[0]->id, 'User is logged in after social authentication');
        $this->assertEquals(Auth::user()->socialUsers[0]->scope, ['read', 'write']);
    }

    /*
     * This test:
     * - The Authenticated User Foo has the handler "@foo" on Twitter
     * - @foo has only Read-Only permission on TwUtils.
     * - The user logged out from twitter on another tab
     * - The user logged in to twitter on that other tab with @bar account
     * - @bar doesn't have a TwUtils Account.
     * - The authenticated user on TwUtils is still linked with @foo.
     * - The authenticated user clicked "Write Permission" on TwUtils
     * - TwUtils will redirect to twitter, and twitter is on @bar account.
     *
     * This test will verify that @bar is differentiated than the @foo user,
     * The @bar is a completely new user for TwUtils. The @foo user should
     * still be in only Read-Only permission on TwUtils. and the @bar user
     * should be now logged in and has the write permission.
     */
    public function test_twitter_read_write_for_new_user()
    {
        // @foo
        $response = $this->getTwitterResponse('twitter.callback', ['getNickname' => 'foo']);

        $this->assertEquals(1, Auth::id());
        $this->assertEquals(Auth::user()->id, User::all()[0]->id);
        $this->assertEquals(Auth::user()->socialUsers[0]->scope, ['read']);

        // @foo is going for "write" permission, but the returned user is @bar.
        $this->getTwitterResponse('twitter.rw.callback', ['getId' => '789', 'getNickname' => 'bar']);

        // @bar should be logged in
        $this->assertEquals(Auth::user()->socialUsers[0]->nickname, 'bar');

        // the newly associated social user should be related to @bar.
        $this->assertEquals(2, SocialUser::find(2)->user_id);

        // @bar should be a completely different user other than @foo.
        $this->assertEquals(Auth::user()->id, User::with('socialUsers')->get()[1]->id);

        // @bar should have read and write permissions.
        $this->assertEquals(Auth::user()->socialUsers[0]->scope, ['read', 'write']);
    }

    public function test_twitter_dont_duplicate_social_users_same_service()
    {
        $socialUser = factory(SocialUser::class)->create(['token' => '12345111', 'social_user_id' => '123']);
        $response = $this->getTwitterResponse();

        // '1234' the same token in the mock
        $this->assertEquals(SocialUser::find(1)->token, '1234', 'Update the social user with the new provided data after login');
        $this->assertEquals(User::all()->count(), 1);
        $this->assertEquals(SocialUser::all()->count(), 1);
        $this->assertEquals(Auth::user()->id, User::all()[0]->id, 'User is logged in after social authentication');
    }

    public function test_twitter_dont_duplicate_social_users_same_nickname()
    {
        $this->getTwitterResponse('twitter.callback', ['getId' => 123, 'getNickname' => 'Pseudo']);

        auth()->logout();

        $this->getTwitterResponse('twitter.callback', ['getId' => 456, 'getNickname' => 'pseuDo']);

        $this->assertEquals(User::all()->count(), 2);
        $this->assertEquals(SocialUser::all()->count(), 2);
        // it's ok to duplicate "SocialUser" model nicknames
        $this->assertEquals(SocialUser::all()->pluck('nickname')->implode(','), 'Pseudo,pseuDo');

        // But it's not ok to duplicate "User" model nicknames..
        $this->assertNotEquals('pseudo,pseudo', User::all()->pluck('username')->implode(','));
        $this->assertEquals('pseudo', User::find(1)->username);
        $this->assertEquals('pseudo_1', User::find(2)->username);
        $this->assertEquals(Auth::user()->socialUsers[0]->id, User::find(2)->socialUsers[0]->id);
    }

    public function test_relying_on_user_ids_not_screen_names()
    {
        $this->getTwitterResponse('twitter.callback', ['getId' => 123, 'getNickname' => 'Pseudo']);

        auth()->logout();

        $this->getTwitterResponse('twitter.callback', ['getId' => 456, 'getNickname' => 'Pseudo']);

        $this->assertEquals(User::all()->count(), 2);
        $this->assertEquals(SocialUser::all()->count(), 2);
        $this->assertEquals('pseudo,pseudo_1', User::all()->pluck('username')->implode(','));
    }

    public function test_twitter_multiple_logins_do_nothing()
    {
        $appUser = factory(User::class)->create();

        $this->actingAs($appUser);

        $socialUser = factory(SocialUser::class)->create(['user_id' => $appUser->id, 'token' => 1234, 'social_user_id' => 123]);

        $this->getTwitterResponse();
        $this->getTwitterResponse();
        $this->getTwitterResponse();

        $this->assertEquals(SocialUser::find(1)->token, '1234'); // the same token in the mock
        $this->assertEquals(SocialUser::find(1)->social_user_id, 123); // the same id in the mock

        $this->assertEquals(SocialUser::all()->count(), 1);
        $this->assertEquals(User::all()->count(), 1);
        $this->assertEquals(Auth::user()->id, User::first()->fresh()->id, 'User is logged in after social authentication');
    }

    public function test_twitter_read_denied_request()
    {
        $response = $this->get(route('twitter.callback').'?denied=123');
        $response->assertRedirect('/');
        $response->assertStatus(302);
        $response->assertSessionHas('message');
    }

    public function test_twitter_read_write_denied_request()
    {
        $response = $this->get(route('twitter.rw.callback').'?denied=123');
        $response->assertRedirect('/');
        $response->assertStatus(302);
        $response->assertSessionHas('message');
    }

    public function test_twitter_read_denied_request_for_authenticated_user()
    {
        $this->getTwitterResponse();

        $response = $this->get(route('twitter.callback').'?denied=123');
        $response->assertRedirect('/app');
        $response->assertStatus(302);
        $response->assertSessionHas('message');
    }

    public function test_twitter_read_write_denied_request_for_authenticated_user()
    {
        $this->getTwitterResponse();

        $response = $this->get(route('twitter.rw.callback').'?denied=123');
        $response->assertRedirect('/app');
        $response->assertStatus(302);
        $response->assertSessionHas('message');
    }

    public function test_twitter_login_redirects()
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirect')
        ->once();

        Socialite::shouldReceive('driver')
        ->with('twitter')
        ->andReturn($provider);

        $response = $this->get(route('twitter.login'));
    }

    public function test_twitter_read_write_login_redirects()
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirect')
        ->once();

        Socialite::shouldReceive('driver')
        ->with('twitter')
        ->andReturn($provider);

        $response = $this->get(route('twitter.rw.login'));
    }

    public function getTwitterResponse($route = 'twitter.callback', $overrideUser = [])
    {
        $user = $overrideUser + [
            'token'       => '1234',
            'tokenSecret' => '12345',
            'getId'       => 123,
            'getEmail'    => Str::random(10).'@test.com',
            'getNickname' => 'Pseudo',
            'getName'     => 'Mohannad Najjar :D',
            'getAvatar'   => 'https://en.gravatar.com/userimage',
        ];

        $abstractUser = Mockery::mock('Laravel\Socialite\One\User');
        $abstractUser->token = $user['token'];
        $abstractUser->tokenSecret = $user['tokenSecret'];
        $abstractUser->shouldReceive('getId')
        ->andReturn($user['getId'])
        ->shouldReceive('getEmail')
        ->andReturn($user['getEmail'])
        ->shouldReceive('getNickname')
        ->andReturn($user['getNickname'])
        ->shouldReceive('getName')
        ->andReturn($user['getName'])
        ->shouldReceive('getAvatar')
        ->andReturn($user['getAvatar']);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('twitter')->andReturn($provider)->byDefault();

        $response = $this->get(route($route));

        return $response;
    }
}

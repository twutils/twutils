<?php

namespace Tests\Feature;

use App\Jobs\CleanLikesJob;
use App\Jobs\FetchLikesJob;
use App\SocialUser;
use App\Task;
use App\Tweet;
use App\User;
use Config;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\IntegrationTestCase;

class AddTasksTest extends IntegrationTestCase
{
    public function test_basic_route_exist()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->postJson('/api/likes');
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function test_first_letter_case_insensitive_task_names()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->postJson('/api/Likes');
        $response->assertStatus(200);

        $response = $this->postJson('/api/LIKES');
        $response->assertStatus(400);
    }

    public function test_basic_undefined_task()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->postJson('/api/foobar');
        $response->assertStatus(400);
    }

    public function test_refuse_unauthorized_related_task()
    {
        Bus::fake();

        $this->logInSocialUser('api');

        $task = factory(Task::class)->create([
            'socialuser_id' => auth()->user()->socialUsers[0]->id, // owner
        ]);

        $this->logInSocialUser('api');

        $this->bindTwitterConnector([]);
        $response = $this->postJson('/api/likes/'.$task->id);
        $response->assertStatus(401);
    }

    public function test_add_likes_task()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->postJson('/api/likes');
        $response->assertStatus(200);

        $this->assertTaskCount(1);
    }

    public function test_add_entities_user_tweets_task()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->postJson('/api/entitiesUserTweets');
        $response->assertStatus(200);

        $this->assertTaskCount(1);
    }

    public function test_add_user_tweets_task()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->postJson('/api/userTweets');
        $response->assertStatus(200);

        $this->assertTaskCount(1);
    }

    public function test_add_followings_task()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->postJson('/api/following');
        $response->assertStatus(200);

        $this->assertTaskCount(1);
    }

    public function test_add_destroy_likes_task()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUserForDestroyLikes();
        $response = $this->postJson('/api/likes');
        $response->assertStatus(200);

        $response = $this->postJson('/api/destroyLikes/1');
        $response->assertStatus(200);

        $this->assertTaskCount(2);
    }

    public function test_add_destroy_tweets_task_with_settings()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUserForDestroyTweets();
        $response = $this->postJson('/api/userTweets');
        $response->assertStatus(200);

        $response = $this->postJson('/api/destroyTweets/1', ['settings' => [
            'end_date' => '2018-09-20',
            'id' => 1, // TODO: another test for checking authorization on related task
            'start_date' => '2013-09-22',
            'replies' => true,
            'retweets' => true,
            'tweets' => true,
        ]]);
        $response->assertStatus(200);

        $taskSettings = Task::all()->last()->extra['settings'];

        $this->assertEquals($taskSettings['start_date'], '2013-09-22');
        $this->assertEquals($taskSettings['end_date'], '2018-09-20');
        $this->assertTrue($taskSettings['replies']);
        $this->assertTrue($taskSettings['retweets']);
        $this->assertTrue($taskSettings['tweets']);

        $this->assertTaskCount(2);
    }
}

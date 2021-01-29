<?php

namespace Tests\Feature;

use App\Models\Task;
use Tests\IntegrationTestCase;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Response;
use App\TwUtils\TwitterOperations\FetchLikesOperation;

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

        $task = Task::factory()->create([
            'socialuser_id' => auth()->user()->socialUsers[0]->id, // owner
        ]);

        $this->logInSocialUser('api');

        $this->bindTwitterConnector([]);
        $response = $this->postJson('/api/likes/'.$task->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals(__('messages.task_add_unauthorized_access'), $response->json('errors')[0]);
    }

    public function test_refuse_undefined_operation_task()
    {
        Bus::fake();

        $this->logInSocialUser('api');

        $response = $this->postJson('/api/UndefinedOperation');
        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals(__('messages.task_add_bad_request'), $response->json('errors')[0]);
    }

    public function test_refuse_maximum_limit_of_task_type()
    {
        Bus::fake();

        config()->set('twutils.tasks_limit_per_user', 10);

        $this->logInSocialUser('api');

        $tasks = Task::factory(10)->create([
            'type' => FetchLikesOperation::class,
            'socialuser_id' => auth()->user()->socialUsers[0]->id, // owner
        ]);

        $response = $this->postJson('/api/likes');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals(__('messages.task_add_max_number'), $response->json('errors')[0]);
    }

    public function test_refuse_task_requires_extra_privileges()
    {
        Bus::fake();

        $this->logInSocialUser('api');

        $response = $this->postJson('/api/ManagedDestroyLikes');
        $response->assertStatus(Response::HTTP_UPGRADE_REQUIRED);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals(__('messages.task_add_no_privilege'), $response->json('errors')[0]);
    }

    public function test_refuse_invalid_dates_for_managed_destroy_likes_task()
    {
        Bus::fake();

        $this->logInSocialUserForDestroyLikes();

        $response = $this->postJson('/api/ManagedDestroyLikes', ['settings' => [
            'end_date'   => '2018-59-20',
            'start_date' => '2013-09-22',
            'replies'    => true,
            'retweets'   => true,
            'tweets'     => true,
        ]]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals('The End Date is not a valid date.', $response->json('errors')[0]);
    }

    public function test_refuse_invalid_dates_for_managed_destroy_tweets_task()
    {
        Bus::fake();

        $this->logInSocialUserForDestroyLikes();

        $response = $this->postJson('/api/ManagedDestroyTweets', ['settings' => [
            'end_date'   => '2018-59-20',
            'start_date' => '2013-09-22',
            'replies'    => true,
            'retweets'   => true,
            'tweets'     => true,
        ]]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals('The End Date is not a valid date.', $response->json('errors')[0]);
    }

    public function test_refuse_invalid_dates_for_likes_task()
    {
        Bus::fake();

        $this->logInSocialUserForDestroyLikes();

        $response = $this->postJson('/api/Likes', ['settings' => [
            'end_date'   => '2018-59-20',
            'start_date' => '2013-09-22',
            'replies'    => true,
            'retweets'   => true,
            'tweets'     => true,
        ]]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals('The End Date is not a valid date.', $response->json('errors')[0]);
    }

    public function test_refuse_invalid_dates_for_destroy_likes_task()
    {
        Bus::fake();

        $this->logInSocialUserForDestroyLikes();

        Task::factory()->create([
            'type' => FetchLikesOperation::class,
            'socialuser_id' => auth()->user()->socialUsers[0]->id,
        ]);

        $response = $this->postJson('/api/destroyLikes/1', ['settings' => [
            'end_date'   => '2018-59-20',
            'start_date' => '2013-09-22',
            'replies'    => true,
            'retweets'   => true,
            'tweets'     => true,
        ]]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals('The End Date is not a valid date.', $response->json('errors')[0]);
    }

    public function test_refuse_invalid_targeted_task_for_destroy_likes_task()
    {
        Bus::fake();

        $this->logInSocialUserForDestroyLikes();

        Task::factory()->create([
            'type' => FetchLikesOperation::class,
            'socialuser_id' => auth()->user()->socialUsers[0]->id,
        ]);

        $response = $this->postJson('/api/destroyLikes/5', ['settings' => [
            'end_date'   => '2018-01-20',
            'start_date' => '2013-09-22',
            'replies'    => true,
            'retweets'   => true,
            'tweets'     => true,
        ]]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertEquals(__('messages.task_add_target_not_found'), $response->json('errors')[0]);
    }

    public function test_refuse_has_previous_queued_task()
    {
        Bus::fake();

        $this->logInSocialUserForDestroyLikes();

        $response = $this->postJson('/api/likes');
        $response->assertOk();

        $response = $this->postJson('/api/likes');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertEmpty($response->json('errors'));
        $this->assertEquals(1, $response->json('data')['task_id']);
    }

    public function test_refuse_invalid_managed_by_task_id()
    {
        Bus::fake();

        $this->logInSocialUserForDestroyLikes();

        $response = $this->postJson('/api/destroyLikes', [
            'managedByTaskId' => 1,
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotEmpty($response->json('errors'));
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
            'end_date'   => '2018-09-20',
            'id'         => 1, // TODO: another test for checking authorization on related task
            'start_date' => '2013-09-22',
            'replies'    => true,
            'retweets'   => true,
            'tweets'     => true,
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

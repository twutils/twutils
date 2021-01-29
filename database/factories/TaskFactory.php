<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\SocialUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'socialuser_id' => SocialUser::factory(),
            'type'          => collect([FetchLikesOperation::class, FetchUserTweetsOperation::class])->random(),
            'status'        => 'queued',
            'extra'         => ['settings' => []],
        ];
    }
}

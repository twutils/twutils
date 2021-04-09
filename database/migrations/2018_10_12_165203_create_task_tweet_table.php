<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTweetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_tweet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks', 'id')->onDelete('cascade');
            $table->string('tweet_id_str', 20)->index();
            $table->boolean('favorited');
            $table->boolean('retweeted');
            $table->dateTime('removed')->nullable();
            $table->foreignId('removed_task_id')->nullable()->constrained('tasks', 'id');
            $table->timestamps();
        });

        Schema::table('task_tweet', function (Blueprint $table) {
            $table->foreign('tweet_id_str')->references('id_str')->on('tweets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_tweet');
    }
}

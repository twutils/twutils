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
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->string('tweet_id_str')->index();
            $table->boolean('favorited');
            $table->boolean('retweeted');
            $table->string('attachments_type')->nullable();
            $table->text('attachments_paths')->nullable();
            $table->dateTime('removed')->nullable();
            $table->unsignedInteger('removed_task_id')->nullable();
            $table->timestamps();
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

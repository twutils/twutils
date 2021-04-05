<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawTweetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_tweets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('upload_id');
            $table->string('id_str')->index();
            $table->text('text');

            $table->dateTime('tweet_created_at');

            $table->integer('retweet_count')->nullable();
            $table->integer('favorite_count')->nullable();

            $table->text('extended_entities')->nullable();
            $table->timestamps();

            $table->foreign('upload_id')
                ->references('id')->on('uploads')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upload_tweets');
    }
}

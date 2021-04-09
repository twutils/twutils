<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTweetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->id();
            $table->string('id_str', 20)->index();
            $table->text('text');
            $table->string('lang')->nullable();
            $table->dateTime('tweet_created_at');

            $table->string('tweep_id_str', 20)->index();

            $table->integer('retweet_count')->nullable();

            $table->string('in_reply_to_screen_name')->nullable();
            $table->string('mentions')->nullable();
            $table->string('hashtags')->nullable();
            $table->boolean('is_quote_status');
            $table->string('quoted_status_id_str', 20)->nullable();
            $table->text('quoted_status')->nullable();
            $table->text('quoted_status_permalink')->nullable();

            $table->text('retweeted_status')->nullable();
            $table->integer('favorite_count')->nullable();
            $table->text('extended_entities')->nullable();
            $table->text('entities')->nullable();

            $table->timestamps();
        });

        Schema::table('tweets', function (Blueprint $table) {
            $table->foreign('tweep_id_str')->references('id_str')->on('tweeps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tweets');
    }
}

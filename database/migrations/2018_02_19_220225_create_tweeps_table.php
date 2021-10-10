<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTweepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tweeps', function (Blueprint $table) {
            $table->id();

            // 64 bit unsigned integer
            // ... max value         = 18,446,744,073,709,551,616
            // ... ... digits length = 20
            // @see https://developer.twitter.com/en/docs/twitter-ids
            $table->string('id_str', 20)->index();

            $table->string('screen_name', 15);
            $table->string('name', 255);

            // .. Found a URL avatar that exceeds 270 characters
            // so let's set it for now to the -almost- max URL length
            $table->string('avatar', 2000);

            $table->string('background_color', 6)->nullable();
            $table->string('background_image', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('display_url', 255)->nullable();


            $table->boolean('verified')->default(false);
            $table->boolean('protected')->default(false);

            $table->integer('followers_count')->nullable();
            $table->integer('friends_count')->nullable();
            $table->integer('favourites_count')->nullable();
            $table->integer('statuses_count')->nullable();

            $table->dateTime('tweep_created_at');

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
        Schema::dropIfExists('tweeps');
    }
}

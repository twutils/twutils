<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_users', function (Blueprint $table) {
            $table->increments('id');
            // App/User relation
            $table->integer('user_id')->unsigned();

            // service authentication
            $table->string('social_user_id');
            $table->string('token');
            $table->string('token_secret');
            $table->string('refresh_token')->nullable();
            $table->string('expires_in')->nullable();

            // service user data
            $table->string('nickname');
            $table->string('name');
            $table->string('email');
            $table->text('avatar');
            $table->string('background_color')->nullable();
            $table->string('background_image')->nullable();
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->string('url')->nullable();
            $table->string('display_url')->nullable();
            $table->integer('followers_count')->nullable();
            $table->integer('friends_count')->nullable();
            $table->integer('favourites_count')->nullable();
            $table->integer('statuses_count')->nullable();

            // permissions scope: 'read' ? 'write' ? 'dm' ?
            $table->string('scope');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_users');
    }
}

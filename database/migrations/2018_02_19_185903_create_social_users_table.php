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
            $table->id();

            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');

            // twitter authentication
            $table->string('social_user_id', 25);
            $table->string('token', 50);
            $table->string('token_secret', 45);

            // twitter user data
            $table->string('nickname', 15);
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('avatar', 50);

            $table->string('background_color', 6)->nullable();
            $table->string('background_image', 50)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('display_url', 255)->nullable();

            $table->integer('followers_count')->nullable();
            $table->integer('friends_count')->nullable();
            $table->integer('favourites_count')->nullable();
            $table->integer('statuses_count')->nullable();

            // permissions scope: ['read'] or ['read','write']
            $table->string('scope', 30);
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
        Schema::dropIfExists('social_users');
    }
}

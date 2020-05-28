<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditStringColumnsLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      Schema::table('users', function (Blueprint $table) {
         $table->string('name', 255)->change();
         $table->string('username', 255)->change();
         $table->string('email', 255)->change()->nullable();
      });

      Schema::table('social_users', function (Blueprint $table) {
         $table->string('social_user_id', 255)->change();
         $table->string('token', 255)->change();
         $table->string('token_secret', 255)->change();
         $table->string('refresh_token', 255)->change()->nullable();
         $table->string('expires_in', 255)->change()->nullable();
         $table->string('nickname', 255)->change();
         $table->string('name', 255)->change();
         $table->string('email', 255)->change();
         $table->string('background_color', 255)->change()->nullable();
         $table->string('background_image', 255)->change()->nullable();
         $table->string('location', 255)->change()->nullable();
         $table->string('description', 255)->change()->nullable();
         $table->string('url', 255)->change()->nullable();
         $table->string('display_url', 255)->change()->nullable();
         $table->string('scope', 255)->change();
      });

      Schema::table('jobs', function (Blueprint $table) {
         $table->string('queue', 255)->change();
      });

      Schema::table('tasks', function (Blueprint $table) {
         $table->string('type', 255)->change();
         $table->string('status', 255)->change();
      });

      Schema::table('tweets', function (Blueprint $table) {
         $table->string('id_str', 255)->change();
         $table->string('lang', 255)->change()->nullable();
         $table->string('in_reply_to_screen_name', 255)->change()->nullable();
         $table->string('mentions', 255)->change()->nullable();
         $table->string('hashtags', 255)->change()->nullable();
         $table->string('quoted_status_id_str', 255)->change()->nullable();
      });

      Schema::table('followings', function (Blueprint $table) {
         $table->string('tweep_id_str', 255)->change();
      });

      Schema::table('tweeps', function (Blueprint $table) {
         $table->string('id_str', 255)->change();
         $table->string('screen_name', 255)->change();
         $table->string('name', 255)->change();
         $table->string('background_color', 255)->change()->nullable();
         $table->string('background_image', 255)->change()->nullable();
         $table->string('location', 255)->change()->nullable();
         $table->string('description', 255)->change()->nullable();
         $table->string('url', 255)->change()->nullable();
         $table->string('display_url', 255)->change()->nullable();
      });

      Schema::table('followers', function (Blueprint $table) {
         $table->string('tweep_id_str', 255)->change();
      });

      Schema::table('task_tweet', function (Blueprint $table) {
         $table->string('tweet_id_str', 255)->change();
      });

      Schema::table('issues', function (Blueprint $table) {
         $table->string('name', 255)->change();
         $table->string('email', 255)->change();
         $table->string('purpose', 255)->change();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

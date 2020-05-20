<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->increments('id');

            $table->string('id_str')->index();

            $table->string('screen_name');
            $table->string('name');
            $table->text('avatar');
            $table->string('background_color')->nullable();
            $table->string('background_image')->nullable();
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->string('url')->nullable();
            $table->string('display_url')->nullable();
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

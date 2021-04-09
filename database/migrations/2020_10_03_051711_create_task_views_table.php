<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')->constrained('tasks', 'id')->onDelete('cascade');

            $table->unsignedInteger('count')->nullable();
            $table->unsignedInteger('tweets_text_only')->nullable();
            $table->unsignedInteger('tweets_with_photos')->nullable();
            $table->unsignedInteger('tweets_with_videos')->nullable();
            $table->unsignedInteger('tweets_with_gifs')->nullable();

            $table->json('months')->nullable();

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
        Schema::dropIfExists('task_views');
    }
}

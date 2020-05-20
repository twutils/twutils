<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignkeyToTaskTweets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_tweet', function (Blueprint $table) {
            $table->foreign('task_id')
            ->references('id')->on('tasks')
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
        Schema::table('table_tweet', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
        });
    }
}

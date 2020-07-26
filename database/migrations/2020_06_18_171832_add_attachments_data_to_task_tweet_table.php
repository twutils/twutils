<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentsDataToTaskTweetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_tweet', function (Blueprint $table) {
            $table->integer('attachment_size')->nullable();
            $table->string('attachment_status', 10)->nullable(); // 'started', 'success', 'broken'

            $table->dateTime('attachment_started_at')->nullable();
            $table->dateTime('attachment_success_at')->nullable();
            $table->dateTime('attachment_broken_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_tweet', function (Blueprint $table) {
            $table->dropColumn('attachment_size');
            $table->dropColumn('attachment_status');

            $table->dropColumn('attachment_started_at');
            $table->dropColumn('attachment_success_at');
            $table->dropColumn('attachment_broken_at');
        });
    }
}

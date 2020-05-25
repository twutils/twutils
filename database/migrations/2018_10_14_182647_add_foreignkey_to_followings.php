<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignkeyToFollowings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('followings', function (Blueprint $table) {
//            $table->foreign('tweep_id_str')->references('id_str')->on('tweeps');

            $table->foreign('task_id')
            ->references('id')->on('tasks')
            ->onDelete('cascade');
        });
        Schema::table('followers', function (Blueprint $table) {
//            $table->foreign('tweep_id_str')->references('id_str')->on('tweeps');

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
        Schema::table('followings', function (Blueprint $table) {
//            $table->dropForeign(['tweep_id_str']);
            $table->dropForeign(['task_id']);
        });
        Schema::table('followers', function (Blueprint $table) {
//            $table->dropForeign(['tweep_id_str']);
            $table->dropForeign(['task_id']);
        });
    }
}

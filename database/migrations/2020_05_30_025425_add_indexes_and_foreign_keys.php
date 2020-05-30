<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesAndForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status');
            $table->index('socialuser_id');
        });

        Schema::table('followings', function (Blueprint $table) {
            $table->foreign('tweep_id_str')->references('id_str')->on('tweeps');
        });

        Schema::table('followers', function (Blueprint $table) {
            $table->foreign('tweep_id_str')->references('id_str')->on('tweeps');
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
        //
    }
}

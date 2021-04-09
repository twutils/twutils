<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFollowingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks', 'id')->onDelete('cascade');

            $table->string('tweep_id_str', 20)->index();

            $table->boolean('followed_by')->default(false);
        });

        Schema::table('followings', function (Blueprint $table) {
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
        Schema::dropIfExists('followings');
    }
}

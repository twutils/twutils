<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('downloads', function (Blueprint $table) {

            $table->id();
            $table->unsignedInteger('task_id');
            $table->string('type', 20); // 'html', 'excel', 'htmlEntities'
            $table->string('status', 10); // 'initial', 'started', 'success', 'broken'

            $table->dateTime('started_at')->nullable();
            $table->dateTime('broken_at')->nullable();
            $table->dateTime('success_at')->nullable();

            $table->timestamps();

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
        Schema::dropIfExists('downloads');
    }
}

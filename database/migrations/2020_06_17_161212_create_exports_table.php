<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('task_id');
            $table->string('type', 20); // 'html', 'excel', 'htmlEntities'
            $table->string('status', 10); // 'initial', 'started', 'success', 'broken'
            $table->string('filename')->nullable();
            $table->unsignedInteger('size')->nullable();

            $table->dateTime('started_at')->nullable();
            $table->dateTime('broken_at')->nullable();
            $table->dateTime('success_at')->nullable();

            $table->unsignedInteger('progress')->nullable();
            $table->unsignedInteger('progress_end')->nullable();

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
        Schema::dropIfExists('exports');
    }
}

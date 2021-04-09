<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('socialuser_id')->index()->constrained('social_users', 'id')->onDelete('cascade');

            $table->string('type', 255)->index();
            $table->string('status', 10)->index();

            $table->text('extra')->nullable();
            $table->text('exception')->nullable();

            $table->foreignId('targeted_task_id')->nullable()->index()->constrained('tasks', 'id')->nullOnDelete();

            $table->foreignId('managed_by_task_id')->nullable()->index()->constrained('tasks', 'id')->nullOnDelete();

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
        Schema::dropIfExists('tasks');
    }
}

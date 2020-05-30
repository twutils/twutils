<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTweetsTableTweepIdStr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tweets', function (Blueprint $table) {
            $table->string('tweep_id_str')->default(10000)->index();
        });

        if (! app()->runningUnitTests())
        {
            DB::statement('UPDATE tweets set tweets.tweep_id_str = (select tweeps.id_str from tweeps where tweeps.id = tweep_id)');
        }

        Schema::table('tweets', function (Blueprint $table) {
            $table->dropColumn('tweep_id');
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

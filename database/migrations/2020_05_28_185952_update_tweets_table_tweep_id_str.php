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
            // Map to the new structure
            DB::statement('UPDATE tweets set tweets.tweep_id_str = (select tweeps.id_str from tweeps where tweeps.id = tweep_id)');

            // Remove Duplicates
            DB::statement('delete from tweets where id in (select * from (select distinct id from tweets, (select tweets.id_str, count(tweets.id_str) as count  from tweets group by tweets.id_str) as subquery where subquery.count > 1 and tweets.id_str = subquery.id_str  and tweets.id < (select max(tweets_max.id) from tweets tweets_max where tweets_max.id_str = tweets.id_str )) temp)');

            // Generic Cleaning
            (new \App\Jobs\CleaningAllTweetsAndTweeps)->handle();
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

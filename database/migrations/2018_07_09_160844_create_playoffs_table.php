<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayoffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_offs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('competition_id');
            $table->integer('games')->default(1); // amount of games
            $table->integer('step')->default(1);
            $table->integer('hometeam_id')->nullable();
            $table->integer('awayteam_id')->nullable();
            $table->integer('next_playoff_id')->nullable();
            $table->integer('hometeam_league_id')->nullable(); // only for first step
            $table->integer('awayteam_league_id')->nullable(); // only for first step
            $table->integer('hometeam_league_place')->nullable(); // only for first step
            $table->integer('awayteam_league_place')->nullable(); // only for first step
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
        Schema::dropIfExists('play_offs');
    }
}

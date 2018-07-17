<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hometeam_id');
            $table->integer('awayteam_id');
            $table->integer('round')->nullable();
            $table->integer('competition_id')->nullable();
            $table->integer('league_id')->nullable();
            $table->integer('playoff_id')->nullable();
            $table->dateTime('starting_time');
            $table->enum('status', [
                'not_started',
                'first_half_playing',
                'pause',
                'second_half_playing',
                'first_half_overtime_playing',
                'second_half_overtime_playing',
                'penalties_playing',
                'ended',
            ]);
            $table->integer('hometeam_score_halftime')->default(0);
            $table->integer('awayteam_score_halftime')->default(0);
            $table->integer('hometeam_score_fulltime')->default(0);
            $table->integer('awayteam_score_fulltime')->default(0);
            $table->integer('hometeam_score_first_half_overtime')->default(0);
            $table->integer('awayteam_score_first_half_overtime')->default(0);
            $table->integer('hometeam_score_second_half_overtime')->default(0);
            $table->integer('awayteam_score_second_half_overtime')->default(0);
            $table->integer('hometeam_penalties_score')->default(0);
            $table->integer('awayteam_penalties_score')->default(0);
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
        Schema::dropIfExists('games');
    }
}

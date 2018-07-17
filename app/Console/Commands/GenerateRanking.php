<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ranking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $teams = \App\Team::all();

        foreach ($teams as $team) {
            $ranking_points = $team->points;
            $teamRank = \App\TeamRanking::where('team_id', $team->id)->first();
            if (!$teamRank) {
                $teamRank = new \App\TeamRanking();
                $teamRank->team_id = $team->id;
            }
            $teamRank->ranking_points = $ranking_points;
            $teamRank->save();
        }
    }
}

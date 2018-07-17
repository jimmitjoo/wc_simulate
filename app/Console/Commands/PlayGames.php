<?php

namespace App\Console\Commands;

use App\Game;
use Illuminate\Console\Command;

class PlayGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:play';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Play games';

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
        //$games = Game::where('starting_time', '<', date('Y-m-d H:i:s'))->get();
        $games = Game::where('hometeam_ranking_points', '<', 0.1)->where('awayteam_ranking_points', '<', 0.1)->get();
        foreach ($games as $game) {
            playGame($game->id);
        }
    }
}

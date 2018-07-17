<?php

namespace App\Console\Commands;

use App\Competition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PlayWorldCup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play:worldcup';

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
        Artisan::call('generate:worldcup');

        $wc = Competition::where('name', 'like', 'World Cup %')->latest()->first();

        Artisan::call('games:play');

        // Generate ranking after qualifications
        Artisan::call('generate:ranking');

        Artisan::call('generate:worldcup');
        Artisan::call('games:play');

        // Round of 16
        Artisan::call('generate:playoffs', ['compId' => $wc->id]);
        Artisan::call('games:play');

        // Quaters
        Artisan::call('generate:playoffs', ['compId' => $wc->id]);
        Artisan::call('games:play');

        // Semis
        Artisan::call('generate:playoffs', ['compId' => $wc->id]);
        Artisan::call('games:play');

        // Final
        Artisan::call('generate:playoffs', ['compId' => $wc->id]);
        Artisan::call('games:play');

        // Generate ranking after cup
        Artisan::call('generate:ranking');

        // Generate rank
    }
}

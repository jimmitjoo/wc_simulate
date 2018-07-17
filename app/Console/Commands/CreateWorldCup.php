<?php

namespace App\Console\Commands;

use App\Competition;
use App\CompetitionTeam;
use App\Confederation;
use App\Team;
use Illuminate\Console\Command;

class CreateWorldCup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:worldcup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new World Cup with Qualifications';

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
        $getExistingWorldCup = Competition::where('ranking_multiplier', 4.0)
            ->where('competition_generated', 0)
            //->whereDate('start_date', date('Y-m-d', strtotime('+3 days')))
            ->first();

        if ($getExistingWorldCup) {
            // Todo: get group winners from qualification
            $qualifies = Competition::where('qualifying_competition_id', $getExistingWorldCup->id)->get();
            foreach ($qualifies as $qualify) {
                foreach ($qualify->leagues as $league) {

                    $competitionTeam = new CompetitionTeam();
                    $competitionTeam->competition_id = $getExistingWorldCup->id;
                    $competitionTeam->team_id = $league->teams->first()->team->id;
                    $competitionTeam->save();
                }
            }
            generateCompetition($getExistingWorldCup->id, 8, '18:00', true, 1);

            return;
        }


        $hosts = Team::where('confederation_id', '!=', null)->inRandomOrder()->first();

        $name = 'World Cup ' . $hosts->name;

        $daysOfSeason = 91;
        $firstSeasonStart = '2018-07-01';
        $season = ceil(ceil(round(time() - strtotime($firstSeasonStart)) / (60 * 60 * 24)) / $daysOfSeason);
        if ($season % 4 !== 1) return;

        $startDateOfQualifications = date('Y-m-d', strtotime(date($firstSeasonStart) . ' +' . (($daysOfSeason * $season) + 40) . ' days'));
        $endDateOfQualifications = date('Y-m-d', strtotime($startDateOfQualifications . ' + 37 days'));

        $startWorldCup = date('Y-m-d', strtotime($endDateOfQualifications . ' + 7 days'));
        $endWorldCup = date('Y-m-d', strtotime($endDateOfQualifications . ' + 21 days'));

        //

        // Todo: Skapa mästerskapets slutspel
        $wc = new Competition();
        $wc->name = $name;
        $wc->ranking_multiplier = 4.0;
        $wc->start_date = $startWorldCup;
        $wc->end_date = $endWorldCup;
        $wc->save();

        // Add hosts to competition
        $competitionTeam = new CompetitionTeam();
        $competitionTeam->competition_id = $wc->id;
        $competitionTeam->team_id = $hosts->id;
        $competitionTeam->save();

        // Todo: Skapa kvalificering för varje confederation
        $confederations = Confederation::all();
        foreach ($confederations as $confederation) {
            $competition = new Competition();
            $competition->name = $confederation->short_name . ' Qualifications to ' . $name;
            $competition->ranking_multiplier = 2.5;
            $competition->qualifying_competition_id = $wc->id;
            $competition->start_date = $startDateOfQualifications;
            $competition->end_date = $endDateOfQualifications;
            $competition->save();

            // Todo: Lägg till alla landslag i kvalet
            foreach ($confederation->federations as $federation) {
                if ($federation->id == $hosts->id) continue;

                $competitionTeam = new CompetitionTeam();
                $competitionTeam->competition_id = $competition->id;
                $competitionTeam->team_id = $federation->id;
                $competitionTeam->save();
            }

            $amountOfGroupsInQualifications = 0;
            if ($confederation->short_name == 'AFC') {
                $amountOfGroupsInQualifications = 6;
                $time = '21:00';
            }
            if ($confederation->short_name == 'CAF') {
                $amountOfGroupsInQualifications = 8;
                $time = '16:00';
            }
            if ($confederation->short_name == 'CONCACAF') {
                $amountOfGroupsInQualifications = 5;
                $time = '09:00';
            }
            if ($confederation->short_name == 'CONMEBOL') {
                $amountOfGroupsInQualifications = 2;
                $time = '11:00';
            }
            if ($confederation->short_name == 'OFC') {
                $amountOfGroupsInQualifications = 2;
                $time = '02:00';
            }
            if ($confederation->short_name == 'UEFA') {
                $amountOfGroupsInQualifications = 8;
                $time = '18:00';
            }

            // Todo: Generera grupper & spelscheman för varje kval
            generateCompetition($competition->id, $amountOfGroupsInQualifications, $time, false, 2);
        }


        // Todo: Skapa trigger för att plocka alla kvalvinnare in i slutspelet & generera detta.
        // generateCompetition($competition->id, $amountOfGroupsInQualifications, true, 1);


    }
}

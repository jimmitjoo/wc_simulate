<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    /*    for ($i=169;$i<207;$i++) {
           $ct = new \App\CompetitionTeam();
           $ct->competition_id = 6;
           $ct->team_id = $i;
           $ct->save();
        }*/

    $worldCups = \App\Competition::where('ranking_multiplier', 4.0)->paginate(15);

    return view('home', ['worldCups' => $worldCups]);
});

Route::get('gen_qualify/{conf_id}/{comp_id}/{groups?}', function ($conf_id, $comp_id, $groups) {

    $teams = \App\Team::where('confederation_id', $conf_id)->get();

    foreach ($teams as $team) {
        $ct = \App\CompetitionTeam::where('competition_id', $comp_id)->where('team_id', $team->id)->first();
        if (!$ct) {
            $ct = new \App\CompetitionTeam();
            $ct->competition_id = $comp_id;
            $ct->team_id = $team->id;
            $ct->save();
        }
    }

    generateCompetition($comp_id, $groups);

});

Route::get('ranking', function() {
    $ranks = \App\TeamRanking::orderBy('ranking_points', 'desc')->get();

    echo '<table>';
    $i=1;
    foreach ($ranks as $rank) {
        echo '<tr>';
            echo '<td>'.$i.'</td>';
            echo '<td>'.$rank->team->name.'</td>';
            echo '<td>'.$rank->ranking_points.'</td>';
        echo '</tr>';
        $i++;
    }
    echo '</table>';
});

Route::get('generate_playoffs/{comp_id}', function ($comp_id) {
    generatePlayoffs($comp_id);
});

Route::get('play_games', function () {

    $games = \App\Game::where('starting_time', '<', date('Y-m-d H:i:s'))->get();
    foreach ($games as $game) {
        playGame($game->id);
    }
});

Route::get('/competitions/generate/{competition}/{groups?}', 'CompetitionsController@generate');
Route::get('/competitions/{competition}', 'CompetitionsController@show');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

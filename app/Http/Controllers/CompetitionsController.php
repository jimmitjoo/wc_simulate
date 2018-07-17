<?php

namespace App\Http\Controllers;

use App\Competition;
use Illuminate\Http\Request;

class CompetitionsController extends Controller
{
    public function generate(Competition $competition, $groups = null)
    {
        generateCompetition($competition->id, $groups);
    }

    public function show(Competition $competition)
    {
        return view('competitions.show', ['competition' => $competition]);

        echo '<h1>' . $competition->name . '</h1>';


        if (count($competition->playoffs)) {
            echo '<h3>Play Offs</h3>';
            $currentStep = null;
            foreach ($competition->playoffs as $playoff) {
                if ($currentStep != $playoff->step) {
                    if ($currentStep != null) echo '</ul>';
                    $currentStep = $playoff->step;
                    echo '<h4>' . playoffName($playoff->step) . '</h4>';
                    echo '<ul>';
                }

                foreach ($playoff->po_games as $game) {
                    echo '<li>' . $game->starting_time . ': ' . $game->hometeam->name . ' ';
                    if ($game->status == 'ended') echo $game->hometeam_score_fulltime;
                    echo ' - ';
                    if ($game->status == 'ended') echo $game->awayteam_score_fulltime;
                    echo ' ' . $game->awayteam->name . '</li>';
                }
            }
            echo '</ul>';
        }

        foreach ($competition->leagues as $league) {
            echo '<h4>' . $league->name . '</h4>';

            echo '<ul>';
            $round = 0;
            foreach ($league->games as $game) {
                if ($round != $game->round) {
                    $round = $game->round;
                    echo '</ul><ul>';
                }
                echo '<li>' . $game->starting_time . ': ' . $game->hometeam->name . ' ';
                if ($game->status == 'ended') echo $game->hometeam_score_fulltime;
                echo ' - ';
                if ($game->status == 'ended') echo $game->awayteam_score_fulltime;
                echo ' ' . $game->awayteam->name . '</li>';
            }
            echo '</ul>';

            echo '<h5>' . $league->name . ' standings</h5>';
            echo '<table>';
            echo '<thead>';
            echo '<td>Team</td>';
            echo '<td>Won</td>';
            echo '<td>Ties</td>';
            echo '<td>Lost</td>';
            echo '<td>Scored</td>';
            echo '<td>Conceded</td>';
            echo '<td>Points</td>';
            echo '</thead>';
            foreach ($league->teams as $team) {
                echo '<tr>';
                echo '<td>' . $team->team->name . '</td>';
                echo '<td>' . $team->won . '</td>';
                echo '<td>' . $team->tied . '</td>';
                echo '<td>' . $team->lost . '</td>';
                echo '<td>' . $team->scored . '</td>';
                echo '<td>' . $team->conceded . '</td>';
                echo '<td><strong>' . $team->points . '</strong></td>';
                echo '</tr>';
            }
            echo '</table>';


        }
    }
}

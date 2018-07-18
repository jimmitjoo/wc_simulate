<?php
/**
 * Created by PhpStorm.
 * User: jimmiejohansson
 * Date: 2018-07-09
 * Time: 13:25
 */

function playGame($gameId)
{
    $game = \App\Game::find($gameId);
    if ($game->status != 'not_started') return;

    $homeRank = ($game->hometeam->points < 1250) ? 1250 : $game->hometeam->points;
    $awayRank = ($game->awayteam->points < 1250) ? 1250 : $game->awayteam->points;

    $totalRanking = $homeRank + $awayRank;
    $homePercent = 100 * (($homeRank / $totalRanking) + 0.01);

    $homescore = 0;
    $awayscore = 0;

    for ($i = 0; $i < 10; $i++) {
        $homechance = rand(0, round(round($homeRank/100)*10));
        $awaychance = rand(0, round(round($awayRank/100)*10));

        $homedefence = rand(0, round(round($homeRank/100)*10));
        $awaydefence = rand(0, round(round($awayRank/100)*10));

        $chance = rand(0, 100);

        if ($chance < $homePercent && $homechance > $awaydefence && rand(0, 10) > 7) {
            $homescore++;
        }

        if ($chance > $homePercent && $awaychance > $homedefence && rand(0, 10) > 7) {
            $awayscore++;
        }
    }

    $game->status = 'ended';
    $game->hometeam_score_fulltime = $homescore;
    $game->awayteam_score_fulltime = $awayscore;
    $game->save();

    // Om matchen är spelad i en liga
    if ($game->league_id != null) {
        $leagueHomeTeam = \App\LeagueTeam::where('team_id', $game->hometeam_id)->where('league_id', $game->league_id)->first();
        $leagueAwayTeam = \App\LeagueTeam::where('team_id', $game->awayteam_id)->where('league_id', $game->league_id)->first();

        if ($homescore > $awayscore) {
            $leagueHomeTeam->points = $leagueHomeTeam->points + 3;
            $leagueHomeTeam->won = $leagueHomeTeam->won + 1;

            $leagueAwayTeam->points = $leagueAwayTeam->points + 0;
            $leagueAwayTeam->lost = $leagueAwayTeam->lost + 1;

            $homeRankPoint = 3;
            $awayRankPoint = 0;

        } elseif ($homescore == $awayscore) {
            $leagueHomeTeam->points = $leagueHomeTeam->points + 1;
            $leagueHomeTeam->tied = $leagueHomeTeam->tied + 1;

            $leagueAwayTeam->points = $leagueAwayTeam->points + 1;
            $leagueAwayTeam->tied = $leagueAwayTeam->tied + 1;

            $homeRankPoint = 1;
            $awayRankPoint = 1;
        } else {
            $leagueHomeTeam->points = $leagueHomeTeam->points + 0;
            $leagueHomeTeam->lost = $leagueHomeTeam->lost + 1;

            $leagueAwayTeam->points = $leagueAwayTeam->points + 3;
            $leagueAwayTeam->won = $leagueAwayTeam->won + 1;

            $homeRankPoint = 0;
            $awayRankPoint = 3;
        }

        $leagueHomeTeam->scored = $leagueHomeTeam->scored + $homescore;
        $leagueAwayTeam->scored = $leagueAwayTeam->scored + $awayscore;
        $leagueHomeTeam->conceded = $leagueHomeTeam->conceded + $awayscore;
        $leagueAwayTeam->conceded = $leagueAwayTeam->conceded + $homescore;

        $leagueHomeTeam->save();
        $leagueAwayTeam->save();
    }

    if ($game->playoff_id != null) {
        $playOff = \App\PlayOff::find($game->playoff_id);

        if ($homescore > $awayscore) {
            $advancingTeamId = $game->hometeam_id;
        } elseif ($awayscore > $homescore) {
            $advancingTeamId = $game->awayteam_id;
        } else {
            $advancingTeamId = shootPenalties($game->hometeam_id, $game->awayteam_id);

            if ($advancingTeamId == $game->hometeam_id) {
                $game->hometeam_penalties_score = 1;
            } elseif ($advancingTeamId == $game->awayteam_id) {
                $game->awayteam_penalties_score = 1;
            }
        }

        if ($playOff->next_playoff_id != null) {
            $nextPlayOff = \App\PlayOff::find($playOff->next_playoff_id);
            $otherTeamPlayOff = \App\PlayOff::where('next_playoff_id', $playOff->next_playoff_id)->where('id', '!=', $game->playoff_id)->first();

            if ($game->playoff_id < $otherTeamPlayOff->id) {
                $nextPlayOff->hometeam_id = $advancingTeamId;
            } else {
                $nextPlayOff->awayteam_id = $advancingTeamId;
            }
            $nextPlayOff->save();
        }

        if ($homescore > $awayscore) {
            $homeRankPoint = 3;
            $awayRankPoint = 0;

        } elseif ($awayscore > $homescore) {
            $homeRankPoint = 0;
            $awayRankPoint = 3;

        } else {
            $homeRankPoint = 1;
            $awayRankPoint = 1;
        }
    }

    // Todo: Get ranking position of teams

    $rankings = \App\TeamRanking::orderBy('ranking_points', 'desc')->get();

    $i = 1;
    $homeTeamRankPos = 150;
    $awayTeamRankPos = 150;
    foreach ($rankings as $rank) {
        if ($game->hometeam_id == $rank->team_id) $homeTeamRankPos = $i;
        if ($game->awayteam_id == $rank->team_id) $awayTeamRankPos = $i;

        $i++;
    }

    if ($homeTeamRankPos == 1) $homeStrength = 200;
    if ($awayTeamRankPos == 1) $awayStrength = 200;
    if ($homeTeamRankPos > 1 && $homeTeamRankPos < 150) $homeStrength = 200 - $homeTeamRankPos;
    if ($awayTeamRankPos > 1 && $awayTeamRankPos < 150) $awayStrength = 200 - $awayTeamRankPos;

    if (!isset($homeStrength)) $homeStrength = 50;
    if (!isset($awayStrength)) $awayStrength = 50;

    // FIFAs ranking algorithm
    // P = M x I x T
    $homeRankingPoints = $homeRankPoint * $game->competition->ranking_multiplier * $homeStrength;
    $awayRankingPoints = $awayRankPoint * $game->competition->ranking_multiplier * $awayStrength;

    $game->hometeam_ranking_points = $homeRankingPoints;
    $game->awayteam_ranking_points = $awayRankingPoints;
    $game->save();

    echo $game->hometeam->name . ' ' . $homescore . ' - ' . $awayscore . ' ' . $game->awayteam->name;
}

function shootPenalties($ht, $at) : int
{
    $teams = [$ht, $at];
    shuffle($teams);
    return end($teams);

    // penalties
    $homepenalties = 0;
    $awaypenalties = 0;
    for ($i = 0; $i < 5; $i++) {
        $homechance = rand(0, 100);
        $awaychance = rand(0, 100);

        $homedefence = rand(0, 100);
        $awaydefence = rand(0, 100);

        if ($homechance > $awaydefence) {
            $homepenalties++;
        }

        if ($awaychance > $homedefence) {
            $awaypenalties++;
        }
    }

    if ($homepenalties > $awaypenalties) {
        return $ht;
    } elseif ($homepenalties < $awaypenalties) {
        return $at;
    } else {

        $teams = [$ht, $at];
        $res = shuffle($teams);
        $winner = $res[0];

        return $winner;
    }
}

function generatePlayoffs($competitionId)
{
    $playOffs = \App\PlayOff::whereNotNull('hometeam_id')->whereNotNull('awayteam_id')->get();
    $continue = true;
    foreach ($playOffs as $playOff) {

        if (count($playOff->po_games) == 0) {
            $game = new \App\Game();
            $game->hometeam_id = $playOff->hometeam_id;
            $game->awayteam_id = $playOff->awayteam_id;
            $game->starting_time = date('Y-m-d H:i', strtotime('+0 days'));
            $game->playoff_id = $playOff->id;
            $game->competition_id = $competitionId;
            $game->save();

            $continue = false;
        }
    }

    if (!$continue) return;

    $playOffStep = \App\PlayOff::where('competition_id', $competitionId)->where('hometeam_id', null)->where('awayteam_id', null)->orderBy('step')->first();
    $playOffs = \App\PlayOff::where('competition_id', $competitionId)->where('hometeam_id', null)->where('awayteam_id', null)->where('step', $playOffStep->step)->get();

    foreach ($playOffs as $playOff) {

        $homeTeamLeague = \App\League::find($playOff->hometeam_league_id);
        $awayTeamLeague = \App\League::find($playOff->awayteam_league_id);

        $hometeam_id = $homeTeamLeague->teams[$playOff->hometeam_league_place - 1]->team_id;
        $awayteam_id = $awayTeamLeague->teams[$playOff->awayteam_league_place - 1]->team_id;


        $playOff->hometeam_id = $hometeam_id;
        $playOff->awayteam_id = $awayteam_id;
        $playOff->save();

        $game = new \App\Game();
        $game->hometeam_id = $hometeam_id;
        $game->awayteam_id = $awayteam_id;
        $game->starting_time = date('Y-m-d H:i', strtotime('+0 days'));
        $game->playoff_id = $playOff->id;
        $game->competition_id = $competitionId;
        $game->save();
    }

}

function generateCompetition($competitionId, $amountGroups = null, $gameTime, $playoffs = true, $meetings = 2, $startDate = null, $endDate = null)
{
    $competition = \App\Competition::find($competitionId);
    if ($competition->competition_generated) return;

    $competitionTeams = \App\CompetitionTeam::where('competition_id', $competitionId)->get()->sortByDesc('team.points');
    $teams = [];
    foreach ($competitionTeams as $team) {
        $teams[] = $team->team_id;
    }


    if ($amountGroups == null) {
        $teamsPerGroup = 4;
        $amountGroups = ceil(count($teams) / $teamsPerGroup);
    } else {
        $teamsPerGroup = ceil(count($teams) / $amountGroups);
    }

    $groups = [];
    $schedules = [];

    $rankingGroups = [];


    // Divide teams into ranking groups
    for ($i = 0; $i < $teamsPerGroup; $i++) {
        $rankingGroups[$i] = array_slice($teams, ($i * $amountGroups), $amountGroups);
        shuffle($rankingGroups[$i]);
    }

    if ($playoffs && $competition->end_date != null) {
        $groupEndDate = date('Y-m-d', strtotime($competition->end_date . ' -7 days'));
    } elseif ($competition->end_date != null) {
        $groupEndDate = $competition->end_date;
    } else {
        $groupEndDate = date('Y-m-d', strtotime('+3 days'));
    }

    $gzzs = [];
    // After we have shuffled the order of teams in each ranking group we can now create the groups
    $i = 0;
    foreach (range('a', 'z') as $letter) {

        $gr = new \App\League();
        $gr->name = 'Group ' . ucfirst($letter);
        $gr->start_date = ($competition->start_date != null) ? $competition->start_date : date('Y-m-d');
        $gr->end_date = $groupEndDate;
        $gr->competition_id = $competitionId;
        $gr->save();
        $gzzs[] = $gr->id;

        $groupTeams = [];

        for ($l = 0; $l < $teamsPerGroup; $l++) {
            if (isset($rankingGroups[$l])) {
                if (!isset($rankingGroups[$l][$i])) continue;
                $groupTeams[] = $rankingGroups[$l][$i];

                $lt = new \App\LeagueTeam();
                $lt->team_id = $rankingGroups[$l][$i];
                $lt->league_id = $gr->id;
                $lt->save();
            } else {
                $break = true;
            }
        }

        if (count($groupTeams) == 0) {
            try {
                $gr->delete();
            } catch (Exception $e) {
                echo 'Exception!';
            }
        } else {
            $groups[] = [ucfirst($letter) => $groupTeams];
            $schedules[] = [ucfirst($letter) => leagueSchedule($groupTeams, $meetings, $gameTime, $competition->start_date, $groupEndDate, $competitionId, $gr->id)];
        }
        $i++;
        if ($i == $amountGroups || isset($break)) break;
    }

    if (!$playoffs) {
        $competition->competition_generated = true;
        $competition->save();

        return;
    }

    $playOffGames = $amountGroups; // 2 from every group goes to playoff

    $final = new \App\PlayOff();
    $final->competition_id = $competitionId;
    $final->step = 4;
    $final->save();

    // Semi-finals
    if ($playOffGames > 1) {
        $semiIds = [];
        for ($gz = 0; $gz < 2; $gz++) {
            $semi = new \App\PlayOff();
            $semi->competition_id = $competitionId;
            $semi->next_playoff_id = $final->id;
            $semi->step = 3;
            $semi->save();
            $semiIds[] = $semi->id;
        }
    }

    // Quater-finals
    if ($playOffGames > 3) {
        $quaterIds = [];
        for ($gz = 0; $gz < 4; $gz++) {
            $quater = new \App\PlayOff();
            $quater->competition_id = $competitionId;
            $quater->next_playoff_id = $semiIds[floor($gz / 2)];
            $quater->step = 2;
            $quater->save();
            $quaterIds[] = $quater->id;
        }
    }

    // Eigth-part-finals
    if ($playOffGames > 7) {
        for ($gz = 0; $gz < 8; $gz++) {
            $eigth = new \App\PlayOff();
            $eigth->competition_id = $competitionId;
            $eigth->next_playoff_id = $quaterIds[floor($gz / 2)];
            $leagueIds = array_slice($gzzs, floor($gz / 2) * 2, 2);

            $eigth->awayteam_league_id = $leagueIds[0];
            $eigth->hometeam_league_id = $leagueIds[1];

            if ($gz % 2 == 0) {
                $eigth->hometeam_league_place = 1; // 1 or 2
            } else {
                $eigth->hometeam_league_place = 2; // 1 or 2
            }


            if ($gz % 2 == 0) {
                $eigth->awayteam_league_place = 2; // 1 or 2
            } else {
                $eigth->awayteam_league_place = 1; // 1 or 2
            }
            $eigth->step = 1;
            $eigth->save();
        }
    }


    $competition->competition_generated = true;
    $competition->save();
}

function leagueSchedule($teams, $meetings = 2, $gameTime, $firstRoundDate, $lastRoundDate, $competitionId = null, $leagueId = null)
{
    $totalDays = (strtotime($lastRoundDate) - strtotime($firstRoundDate)) / (60 * 60 * 24);

    // randomize the order to get different schedules...
    shuffle($teams);

    // Count the number of teams
    $n = count($teams);

    // If number of team is odd, add a ghost-team...
    if ($n % 2 != 0) {
        $n++;
        $ghost = $n;
    }

    // Number of rounds
    $rounds = ($n - 1) * $meetings;
    $daysBetweenGames = round($totalDays / $rounds);

    $games = [];

    // Loop the rounds...
    for ($r = 1; $r <= $rounds; $r++) {

        // For each round loop teams / 2 ... it takes 2 to tango...
        for ($s = 1; $s <= $n / 2; $s++) {
            // algorithm to take each team "backwards"
            $hometeam = ($s == 1) ? 1 : (($r + $s - 2) % ($n - 1) + 2);
            // algorithm to prevent home and awayteam to be the same
            $awayteam = ($n - 1 + $r - $s) % ($n - 1) + 2;
            // let the venue change after each round... homegame... then awaygame..
            if ($r % 2) {
                $swap = $hometeam;
                $hometeam = $awayteam;
                $awayteam = $swap;
            }
            // never print the ghost-team..
            if (!isset($ghost) || (($hometeam != $ghost) && ($awayteam != $ghost))) {
                /*$game = [
                    'hometeam' => $teams[($hometeam-1)]['name'],
                    'awayteam' => $teams[($awayteam-1)]['name'],
                    'round' => $r,
                    'league_id' => $leagueId,
                    'competition_id' => $competitionId,
                ];*/

                $gm = new \App\Game();
                $gm->hometeam_id = $teams[($hometeam - 1)];
                $gm->awayteam_id = $teams[($awayteam - 1)];
                $gm->starting_time = date('Y-m-d ' . $gameTime, strtotime($firstRoundDate . ' +' . (($r - 1) * $daysBetweenGames) . ' days'));
                $gm->round = $r;
                $gm->league_id = $leagueId;
                $gm->competition_id = $competitionId;
                $gm->save();
            }
        }
    }
}

function playoffName($step)
{
    if ($step == 4) return 'Final';
    if ($step == 3) return 'Semi-finals';
    if ($step == 2) return 'Quater-finals';
    if ($step == 1) return 'Round of 16';
}
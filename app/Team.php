<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    public function getPointsAttribute()
    {
        $recentHomeGames = Game::where('hometeam_id', $this->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get(['starting_time', 'hometeam_ranking_points']);

        $homeGames = Game::where('hometeam_id', $this->id)
            ->orderBy('id', 'desc')
            ->offset(10)
            ->take(15)
            ->get(['starting_time', 'hometeam_ranking_points']);

        $oldHomeGames = Game::where('hometeam_id', $this->id)
            ->orderBy('id', 'desc')
            ->offset(25)
            ->take(25)
            ->get(['starting_time', 'hometeam_ranking_points']);

        $recentAwayGames = Game::where('awayteam_id', $this->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get(['starting_time', 'awayteam_ranking_points']);

        $awayGames = Game::where('awayteam_id', $this->id)
            ->orderBy('id', 'desc')
            ->offset(10)
            ->take(15)
            ->get(['starting_time', 'awayteam_ranking_points']);

        $oldAwayGames = Game::where('awayteam_id', $this->id)
            ->orderBy('id', 'desc')
            ->offset(25)
            ->take(25)
            ->get(['starting_time', 'awayteam_ranking_points']);

        $rankingPoints =
            $this->calculateRankingPoints($oldHomeGames, 5) +
            $this->calculateRankingPoints($oldAwayGames, 5) +
            $this->calculateRankingPoints($homeGames, 2) +
            $this->calculateRankingPoints($awayGames, 2) +
            $this->calculateRankingPoints($recentHomeGames) +
            $this->calculateRankingPoints($recentAwayGames);

        return $rankingPoints;
    }

    /**
     * @param $games
     * @return float
     */
    private function calculateRankingPoints($games, $divide = false)
    {
        $points = 0.0;
        foreach ($games as $game) {
            if (isset($game->hometeam_ranking_points)) {
                $points += $game->hometeam_ranking_points;
            } elseif (isset($game->awayteam_ranking_points)) {
                $points += $game->awayteam_ranking_points;
            }
        }

        if ($divide) $points = $points / $divide;

        return $points;
    }
}

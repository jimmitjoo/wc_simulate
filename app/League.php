<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    protected $with = ['teams', 'games'];

    public function teams()
    {
        return $this->hasMany(LeagueTeam::class, 'league_id', 'id')
            ->orderByDesc('points')
            ->orderByRaw('(scored - conceded) desc')
            ->orderByDesc('scored');
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }
}

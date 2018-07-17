<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompetitionTeam extends Model
{
    protected $with = ['team'];

    public function team()
    {
        return $this->hasOne(Team::class, 'id');
    }
}

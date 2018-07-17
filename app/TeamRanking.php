<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamRanking extends Model
{
    protected $with = ['team'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}

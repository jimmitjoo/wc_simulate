<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $with = ['hometeam', 'awayteam'];

    public function hometeam()
    {
        return $this->belongsTo(Team::class);
    }

    public function awayteam()
    {
        return $this->belongsTo(Team::class);
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}

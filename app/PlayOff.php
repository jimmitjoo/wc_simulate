<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlayOff extends Model
{
    protected $with = ['po_games'];

    public function po_games()
    {
        return $this->hasMany(Game::class, 'playoff_id');
    }
}

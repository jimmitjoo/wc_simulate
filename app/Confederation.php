<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Confederation extends Model
{
    public function federations()
    {
        return $this->hasMany(Team::class);
    }
}

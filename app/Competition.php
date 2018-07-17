<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    protected $with = ['leagues', 'playoffs'];

    public function leagues()
    {
        return $this->hasMany(League::class);
    }

    public function playoffs()
    {
        return $this->hasMany(PlayOff::class)->orderBy('step', 'desc');
    }
}

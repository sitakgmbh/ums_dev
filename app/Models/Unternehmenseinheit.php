<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unternehmenseinheit extends Model
{
    protected $table = 'unternehmenseinheiten';

    protected $fillable = ['name', 'enabled'];

    public function abteilungen()
    {
        return $this->hasMany(Abteilung::class);
    }

    public function konstellationen()
    {
        return $this->hasMany(Konstellation::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abteilung extends Model
{
    protected $table = 'abteilungen';

    protected $fillable = ['name', 'unternehmenseinheit_id', 'enabled'];

    public function unternehmenseinheit()
    {
        return $this->belongsTo(Unternehmenseinheit::class);
    }

    public function konstellationen()
    {
        return $this->hasMany(Konstellation::class);
    }
}

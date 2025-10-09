<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konstellation extends Model
{
    protected $table = "konstellationen";

    protected $fillable = [
        "arbeitsort_id",
        "unternehmenseinheit_id",
        "abteilung_id",
        "funktion_id",
        "enabled",
    ];

    public function arbeitsort()
    {
        return $this->belongsTo(Arbeitsort::class);
    }

    public function unternehmenseinheit()
    {
        return $this->belongsTo(Unternehmenseinheit::class);
    }

    public function abteilung()
    {
        return $this->belongsTo(Abteilung::class);
    }

    public function funktion()
    {
        return $this->belongsTo(Funktion::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funktion extends Model
{
    protected $table = "funktionen";

    protected $fillable = [
		"name",
		"enabled"
	];

    public function konstellationen()
    {
        return $this->hasMany(Konstellation::class);
    }
}

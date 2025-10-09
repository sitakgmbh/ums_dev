<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titel extends Model
{
    protected $table = "titel";

    protected $fillable = [
		"name",
		"enabled"
	];

    public function adUsers()
    {
        return $this->hasMany(AdUser::class);
    }
}

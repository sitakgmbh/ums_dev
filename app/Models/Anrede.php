<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anrede extends Model
{
    protected $table = 'anreden';

    protected $fillable = ['name', 'enabled'];

    public function adUsers()
    {
        return $this->hasMany(AdUser::class);
    }
}

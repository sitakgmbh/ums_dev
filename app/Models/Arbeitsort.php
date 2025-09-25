<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arbeitsort extends Model
{
    protected $table = 'arbeitsorte';

    protected $fillable = ['name', 'enabled'];

    public function konstellationen()
    {
        return $this->hasMany(Konstellation::class);
    }
}

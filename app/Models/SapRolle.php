<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapRolle extends Model
{
    protected $table = 'sap_rollen';

    protected $fillable = [
        'name',
        'enabled',
        'label',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}

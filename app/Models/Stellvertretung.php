<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stellvertretung extends Model
{
    protected $table = "stellvertretungen";

    protected $fillable = [
        "user_id",
        "ad_user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adUser()
    {
        return $this->belongsTo(AdUser::class);
    }
}

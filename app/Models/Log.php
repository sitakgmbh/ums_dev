<?php

namespace App\Models;

use App\Enums\LogLevel;
use App\Enums\LogCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'category',
        'message',
        'context',
    ];

    protected $casts = [
        'level'    => LogLevel::class,
        'category' => LogCategory::class,
    ];
}

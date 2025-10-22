<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'title',
        'remind_at',
        'fcm_token',
        'notified',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'notified' => 'boolean',
    ];
}

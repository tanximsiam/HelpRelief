<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisasterAlert extends Model
{
    //
    protected $fillable = [
        'title',
        'disaster_type',
        'status',
        'description',
        'reported_at',
        'divisions',
        'confirmed',
    ];

    protected $casts = [
        'divisions' => 'array',
        'reported_at' => 'datetime',
    ];

    protected $attributes = [
        'confirmed' => 'pending',
    ];
}

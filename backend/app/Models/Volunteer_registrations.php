<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Volunteer_registrations extends Model
{
    protected $table = 'volunteer_registrations';

    protected $fillable = [
        'user_id', 
        'disaster_id', 
        'ngo_id',
        'status', 
        'registered_at', 
        'availability',
        'skills', 
        'notes'
    ];

}

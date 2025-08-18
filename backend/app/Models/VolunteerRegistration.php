<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolunteerRegistration extends Model
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
    public function ngo()
    {
        return $this->belongsTo(Ngo::class, 'ngo_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AidRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'disaster_id',
        'requester_id',
        'location',
        'aid_type',
        'urgency',
        'description',
        'status',
        'task_id',
        'ngo_remarks',
    ];

    // Get the user who made the aid request.
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    //Scope a query to only include requests with a specific urgency level.

    public function scopeByUrgency($query, $urgency)
    {
        return $query->where('urgency', $urgency);
    }

    //Scope a query to only include requests with a specific status.

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

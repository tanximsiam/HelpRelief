<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'disaster_id',
        'assigned_to',
        'created_by',
        'task_type',
        'aid_request_id',
        'location',
        'start_time',
        'end_time',
        'aid_type',
        'urgency',
        'description',
        'status',
        'ngo_remarks'
    ];

    public function disaster()
    {
        return $this->belongsTo(Disaster::class);
    }

    public function volunteer()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // public function aidRequest()
    // {
    //     return $this->belongsTo(AidRequest::class, 'aid_request_id');
    // }
}


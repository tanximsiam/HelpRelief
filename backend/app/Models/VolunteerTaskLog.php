<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class VolunteerTaskLog extends Model

{
    protected $fillable = [
        'task_id', 'volunteer_id', 'disaster_id', 'status',
        'check_in', 'check_out', 'start_verified_by', 'end_verified_by', 'report'
    ];

    public function task() {
        return $this->belongsTo(Task::class);
    }

    public function volunteer() {
        return $this->belongsTo(User::class, 'volunteer_id');
    }
}


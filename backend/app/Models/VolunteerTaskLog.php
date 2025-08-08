<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerTaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'volunteer_id',
        'disaster_id',
        'status',
        'check_in',
        'check_out',
        'expected_end',
        'start_verified_by',
        'end_verified_by',
        'report',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'expected_end' => 'datetime',
    ];

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }




    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }

    public function startVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'start_verified_by');
    }

    public function endVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'end_verified_by');
    }
}

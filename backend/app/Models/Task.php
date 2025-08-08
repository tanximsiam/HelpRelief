<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AidRequest;
use App\Models\VolunteerTaskLog;

class Task extends Model
{
    use HasFactory;

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
        'ngo_remarks',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Relationships
    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aidRequest(): BelongsTo
    {
        return $this->belongsTo(AidRequest::class);
    }

    public function volunteerTaskLogs(): HasMany
    {
        return $this->hasMany(VolunteerTaskLog::class);
    }
}

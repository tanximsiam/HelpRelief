<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Task;
use App\Models\VolunteerTaskLog;
use App\Models\User;


class Disaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'location',
        'severity',
        'status',
        'occurred_at',
        'resolved_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function volunteerTaskLogs(): HasMany
    {
        return $this->hasMany(VolunteerTaskLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

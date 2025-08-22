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

    // Columns based on migration (2025_07_31_166640_create_disasters_table)
    protected $fillable = [
        'name',
        'disaster_type',
        'location',
        'start_date',
        'severity',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
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

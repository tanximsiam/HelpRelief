<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Disaster;
use App\Models\Ngo;
use App\Models\User;
use App\Models\DonationReport;

class DisasterCampaignAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'disaster_id',
        'ngo_id',
        'assigned_by',
        'status',
        'help_needed',
        'updated_by',
    ];

    // Relationships
    public function disaster()
    {
        return $this->belongsTo(Disaster::class, 'disaster_id');
    }

    public function ngo()
    {
        return $this->belongsTo(Ngo::class, 'ngo_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function donations()
    {
        return $this->hasMany(DonationReport::class, 'campaign_id');
    }
}

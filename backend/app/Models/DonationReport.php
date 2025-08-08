<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationReport extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'disaster_id',
        'ngo_id',
        'aid_type',
        'amount_received',
        'amount_used',
        'usage_breakdown',
        'reporting_period',
        'confirmed',
    ];

    // Relationships
    public function disaster()
    {
        return $this->belongsTo(Disaster::class);
    }

    public function ngo()
    {
        return $this->belongsTo(Ngo::class, 'ngo_id');
    }
}

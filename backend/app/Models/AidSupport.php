<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AidSupport extends Model
{
    protected $table = 'aid_supports';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'user_id', 
        'disaster_id', 
        'campaign_id',
        'aid_type', 
        'quantity', 
        'description',
        'contact', 
        'status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disaster()
    {
        return $this->belongsTo(Disaster::class);
    }

    public function campaign()
    {
        return $this->belongsTo(DisasterCampaignAssignment::class, 'campaign_id');
    }
}
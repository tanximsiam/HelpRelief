<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DisasterCampaignAssignment;

class DonationReport extends Model
    // Already has campaign() relation
{
    //
    use HasFactory;
    protected $fillable = [
        'campaign_id',
        'amount_received_financial',
        'amount_used_financial',
        'amount_received_medical',
        'amount_used_medical',
        'amount_received_resource',
        'amount_used_resource',
        'usage_breakdown',
    ];


    public function campaign()
    {
        return $this->belongsTo(DisasterCampaignAssignment::class, 'campaign_id');
    }
}

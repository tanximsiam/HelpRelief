<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DonationReport;

class DonationReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DonationReport::create([
            'campaign_id'      => 1,
            'amount_received_financial'  => 500000.00,
            'amount_used_financial'      => 350000.00,
            'amount_received_medical'    => 120000.00,
            'amount_used_medical'        => 100000.00,
            'amount_received_resource'   => 80000.00,
            'amount_used_resource'       => 60000.00,
            'usage_breakdown'  => 'food: 200000, shelter: 150000, first_aid_kits: 40000, medicines: 80000, blankets: 30000',
        ]);

        DonationReport::create([
            'campaign_id'      => 2,
            'amount_received_financial'  => 300000.00,
            'amount_used_financial'      => 250000.00,
            'amount_received_medical'    => 150000.00,
            'amount_used_medical'        => 120000.00,
            'amount_received_resource'   => 90000.00,
            'amount_used_resource'       => 70000.00,
            'usage_breakdown'  => 'food: 100000, shelter: 80000, first_aid_kits: 50000, medicines: 70000, blankets: 20000',
        ]);
        DonationReport::create([
            'campaign_id'      => 3,
            'amount_received_financial'  => 400000.00,
            'amount_used_financial'      => 300000.00,
            'amount_received_medical'    => 100000.00,
            'amount_used_medical'        => 90000.00,
            'amount_received_resource'   => 70000.00,
            'amount_used_resource'       => 50000.00,
            'usage_breakdown'  => 'food: 150000, shelter: 120000, first_aid_kits: 30000, medicines: 60000, blankets: 25000',
        ]);
    }
}

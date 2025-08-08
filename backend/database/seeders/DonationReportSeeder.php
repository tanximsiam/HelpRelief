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
            'disaster_id'      => 1,
            'ngo_id'           => 1,
            'aid_type'         => 'financial',
            'amount_received'  => 500000.00,
            'amount_used'      => 350000.00,
            'usage_breakdown'  => json_encode([
                'food' => '200000',
                'shelter' => '150000'
            ]),
            'reporting_period' => 'July 2025',
            'confirmed'        => true,
        ]);

        DonationReport::create([
            'disaster_id'      => 1,
            'ngo_id'           => 1,
            'aid_type'         => 'medical',
            'amount_received'  => 200000.00,
            'amount_used'      => 180000.00,
            'usage_breakdown'  => json_encode([
                'first_aid_kits' => '80000',
                'medicines'      => '100000'
            ]),
            'reporting_period' => 'July 2025',
            'confirmed'        => true,
        ]);
    }
}

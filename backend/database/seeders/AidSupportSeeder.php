<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AidSupport;
class AidSupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       AidSupport::create([
            'user_id' => 1, 
            'disaster_id' => 1, 
            'ngo_id' => 1, 
            'aid_type' => 'financial',
            'quantity' => 1000,
            'description' => 'Financial aid for disaster relief.',
            'contact' => '1234567890',
            'status' => 'processing',
        ]);

        AidSupport::create([
            'user_id' => 3, 
            'disaster_id' => 2, 
            'ngo_id' => 2, 
            'aid_type' => 'medical',
            'quantity' => 50,
            'description' => 'Medical supplies for disaster area.',
            'contact' => '0987654321',
            'status' => 'processing',
        ]);
    }
}

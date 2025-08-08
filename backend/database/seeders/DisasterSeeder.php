<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Disaster;
use Illuminate\Support\Str;

class DisasterSeeder extends Seeder
{
    public function run()
    {
        Disaster::create([
            'id' => 1,
            'name' => 'Cyclone Remal',
            'disaster_type' => 'storm',
            'location' => 'Cox’s Bazar',
            'start_date' => '2025-08-01',
            'status' => 'active',
            'description' => 'A severe tropical storm hit the coastal areas.',
            'created_by' => 1, 
        ]);

        Disaster::create([
            'id' => 2,
            'name' => 'Flood in Sylhet',
            'disaster_type' => 'flood',
            'location' => 'Sylhet',
            'start_date' => '2025-07-20',
            'status' => 'active',
            'description' => 'Floodwaters have inundated large parts of the Sylhet district.',
            'created_by' => 2, 
        ]);

        Disaster::create([
            'id' => 3,
            'name' => 'Earthquake in Dhaka',
            'disaster_type' => 'earthquake',
            'location' => 'Dhaka',
            'start_date' => '2025-06-10',
            'status' => 'closed',
            'description' => 'An earthquake of magnitude 6.5 struck Dhaka city.',
            'created_by' => 3, 
        ]);
    }
}

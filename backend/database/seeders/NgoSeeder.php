<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ngo;

class NgoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ngo::create([
            'name' => 'Helping Hands',
            'description' => 'A NGO focused on disaster relief.',
            'website' => 'https://helpinghands.org',
            'email' => 'contact@helpinghands.org',
            'phone' => '123-456-7890',
            'based_in' => 'Dhaka',
            'registration_no' => '1234-5678',
            'established_year' => 2005,
            'director_name' => 'Alice Johnson',
            'director_phone' => '123-456-7891',
            'num_employees' => 50,
            'logo_url' => 'https://helpinghands.org/logo.png',
            'approved' => true,
        ]);

        Ngo::create([
            'name' => 'Hope Foundation',
            'description' => 'An NGO helping underprivileged children.',
            'website' => 'https://hopefoundation.org',
            'email' => 'info@hopefoundation.org',
            'phone' => '098-765-4321',
            'based_in' => 'Chittagong',
            'registration_no' => '2345-6789',
            'established_year' => 2010,
            'director_name' => 'Bob Smith',
            'director_phone' => '098-765-4322',
            'num_employees' => 100,
            'logo_url' => 'https://hopefoundation.org/logo.png',
            'approved' => true,
        ]);
    }
}


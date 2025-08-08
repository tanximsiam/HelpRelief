<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ngo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NgoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ngo::create([
            'name' => 'ngo1',
            'description' => 'An NGO dedicated to providing education and resources to underprivileged children.',
            'website' => 'http://ngo1.org',
            'email' => 'contact@ngo1.org',
            'phone' => '123-456-7890',
            'based_in' => 'Dhaka, Bangladesh',
            'registration_no' => 'NGO12345',
            'established_year' => 2010,
            'director_name' => 'ngodirector1',
            'director_phone' => '123-456-7890',
            'num_employees' => 50,
            'logo_url' => 'http://ngo1.org/logo.png',
            'approved' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        Ngo::create([
            'name' => 'ngo2',
            'description' => 'A non-profit organization focused on environmental conservation and sustainability.',
            'website' => 'http://ngo2.org',
            'email' => 'contact@ngo2.org',
            'phone' => '234-567-8901',
            'based_in' => 'Chittagong, Bangladesh',
            'registration_no' => 'NGO12346',
            'established_year' => 2015,
            'director_name' => 'ngodirector2',
            'director_phone' => '234-567-8901',
            'num_employees' => 30,
            'logo_url' => 'http://ngo2.org/logo.png',
            'approved' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        Ngo::create([
            'name' => 'ngo3',
            'description' => 'An NGO working to provide healthcare services in rural areas.',
            'website' => 'http://ngo3.org',
            'email' => 'contact@ngo3.org',
            'phone' => '345-678-9012',
            'based_in' => 'Sylhet, Bangladesh',
            'registration_no' => 'NGO12347',
            'established_year' => 2020,
            'director_name' => 'ngodirector3',
            'director_phone' => '345-678-9012',
            'num_employees' => 40,
            'logo_url' => 'http://ngo3.org/logo.png',
            'approved' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}


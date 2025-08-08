<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NgoApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ngo_applications')->insert([
            [
                'organization' => 'ngo1',
                'contact_person' => 'ngostaff1_1',
                'designation' => 'Founder',
                'email' => 'ngostaff1_1@ngo1.org',
                'phone' => '123-456-7890',
                'based_in' => 'Dhaka, Bangladesh',
                'description' => 'An NGO dedicated to providing education and resources to underprivileged children.',
                'status' => 'approved',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'organization' => 'ngo2',
                'contact_person' => 'ngostaff2_1',
                'designation' => 'Executive Director',
                'email' => 'ngostaff2_1@ngo2.org',
                'phone' => '234-567-8901',
                'based_in' => 'Chittagong, Bangladesh',
                'description' => 'A non-profit organization focused on environmental conservation and sustainability.',
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'organization' => 'ngo3',
                'contact_person' => 'ngostaff3_1',
                'designation' => 'Medical Director',
                'email' => 'ngostaff3_1@ngo3.org',
                'phone' => '345-678-9012',
                'based_in' => 'Sylhet, Bangladesh',
                'description' => 'An NGO working to provide healthcare services in rural areas.',
                'status' => 'approved',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\NgoEmailDomain;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NgoEmailDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NgoEmailDomain::create([
            'ngo_id' => 1,
            'domain' => 'ngo1.org',
        ]);
        NgoEmailDomain::create([
            'ngo_id' => 2,
            'domain' => 'ngo2.org',
        ]);
        NgoEmailDomain::create([
            'ngo_id' => 3,
            'domain' => 'ngo3.org',
        ]);
    }
}

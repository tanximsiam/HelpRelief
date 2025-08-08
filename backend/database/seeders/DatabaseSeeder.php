<?php

namespace Database\Seeders;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Nette\Utils\Random;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            NgoApplicationSeeder::class,
            NgoSeeder::class,
            NgoStaffSeeder::class,
            NgoEmailDomainSeeder::class,
            DisasterSeeder::class,
            AidRequestSeeder::class,
            TaskSeeder::class,
            AidSupportSeeder::class,
            VolunteerSeeder::class,
            VolunteerTaskLogsSeeder::class,
        ]);

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

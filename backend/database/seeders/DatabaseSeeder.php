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
        #Ngo::factory(5)->create();
        #User::factory(10)->create();
        
        $this->call([
            NgoSeeder::class,
            UserSeeder::class,
            DisasterSeeder::class,
            AidSupportSeeder::class,
            VolunteerSeeder::class,
        ]);

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

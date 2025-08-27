<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'general1',
            'email' => 'general1@example.com',
            'phone' => '0123456789',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);

        User::create([
            'name' => 'ngostaff1',
            'email' => 'ngostaff1@example.com',
            'phone' => '9876543210',
            'password' => Hash::make('ngopass'),
            'role' => 'ngo_staff',
            'volunteer' => false,
        ]);
        User::create([
            'name' => 'general2',
            'email' => 'general2@example.com',
            'phone' => '5551234567',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);
        User::create([
            'name' => 'admin1',
            'email' => 'admin1@example.com',
            'phone' => '4445556666',
            'password' => Hash::make('adminpass'),
            'role' => 'admin',
            'volunteer' => false,
        ]);
        User::create([
            'name' => 'ngostaff2',
            'email' => 'ngostaff2@example.com',
            'phone' => '3334445555',
            'password' => Hash::make('ngopass'),
            'role' => 'ngo_staff',
            'volunteer' => false,
        ]);
        User::create([
            'name' => 'ngostaff1_1',
            'email' => 'ngostaff1_1@ngo1.org',
            'phone' => '123-456-7890',
            'password' => Hash::make('ngopass'),
            'role' => 'ngo_staff',
            'volunteer' => false,
        ]);
        User::create([
            'name' => 'ngostaff2_1',
            'email' => 'ngostaff2_1@ngo2.org',
            'phone' => '234-567-8901',
            'password' => Hash::make('ngopass'),
            'role' => 'ngo_staff',
            'volunteer' => false,
        ]);
        User::create([
            'name' => 'ngostaff3_1',
            'email' => 'ngostaff3_1@ngo3.org',
            'phone' => '345-678-9012',
            'password' => Hash::make('ngopass'),
            'role' => 'ngo_staff',
            'volunteer' => false,
        ]);

        // Additional general users who can be volunteers
        User::create([
            'name' => 'volunteer1',
            'email' => 'volunteer1@example.com',
            'phone' => '111-222-3333',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);

        User::create([
            'name' => 'volunteer2',
            'email' => 'volunteer2@example.com',
            'phone' => '222-333-4444',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);

        User::create([
            'name' => 'volunteer3',
            'email' => 'volunteer3@example.com',
            'phone' => '333-444-5555',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);

        User::create([
            'name' => 'volunteer4',
            'email' => 'volunteer4@example.com',
            'phone' => '444-555-6666',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);

        User::create([
            'name' => 'volunteer5',
            'email' => 'volunteer5@example.com',
            'phone' => '555-666-7777',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);

        User::create([
            'name' => 'volunteer6',
            'email' => 'volunteer6@example.com',
            'phone' => '666-777-8888',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);

        User::create([
            'name' => 'volunteer7',
            'email' => 'volunteer7@example.com',
            'phone' => '777-888-9999',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);

        User::create([
            'name' => 'volunteer8',
            'email' => 'volunteer8@example.com',
            'phone' => '888-999-0000',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => true,
        ]);
        User::create([
            'name' => 'general110',
            'email' => 'general10@example.com',
            'phone' => '0123456789',
            'password' => Hash::make('userpass'),
            'role' => 'general',
            'volunteer' => false,
        ]);
    }
}

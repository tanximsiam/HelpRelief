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
    }
}
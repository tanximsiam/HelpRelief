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
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone' => '0123456789',
            'password' => Hash::make('password123'),
            'role' => 'general',
            'volunteer' => true,
        ]);
        
        User::create([
            'name' => 'Jane Doe',
            'email' => 'janedoe@example.com',
            'phone' => '9876543210',
            'password' => Hash::make('password456'),
            'role' => 'ngo_staff',
            'volunteer' => false,
        ]);
    }
}
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'gabutmen5@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('gipari909'),
                'email_verified_at' => now(),
                'balance' => 0,
                'role' => 'admin',
            ]
        );
    }
}

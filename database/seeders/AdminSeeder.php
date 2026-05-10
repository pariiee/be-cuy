<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'gabutmen5@gmail.com'],
            [
                'name'               => 'Admin',
                'password'           => Hash::make('gipari909'),
                'email_verified_at'  => now(),
                'balance'            => 0,
                'role'               => 'admin',
            ]
        );

        if (empty($admin->api_token) || ! str_starts_with($admin->api_token, 'wtu_')) {
            $admin->update(['api_token' => 'wtu_' . Str::random(40)]);
        }

        $this->command->info('Admin API Key: ' . $admin->fresh()->api_token);
    }
}

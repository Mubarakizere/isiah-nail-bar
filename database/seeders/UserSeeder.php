<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Provider;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Provider
        $providerUser = User::create([
            'name' => 'Provider User',
            'email' => 'provider@example.com',
            'password' => Hash::make('password'),
            'role' => 'provider',
        ]);

        Provider::create([
            'name' => 'Gloria Nails',
            'email' => 'provider@example.com',
            'phone' => '0781234567',
            'user_id' => $providerUser->id,
        ]);
    }
}

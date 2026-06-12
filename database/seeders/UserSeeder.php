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
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $providerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'provider']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer']);

        // Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        // Provider
        $providerUser = User::create([
            'name' => 'Provider User',
            'email' => 'provider@example.com',
            'password' => Hash::make('password'),
        ]);
        $providerUser->assignRole($providerRole);

        Provider::create([
            'name' => 'Gloria Nails',
            'email' => 'provider@example.com',
            'phone' => '0781234567',
            'user_id' => $providerUser->id,
        ]);
    }
}

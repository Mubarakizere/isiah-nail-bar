<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignRolesToExistingUsers extends Seeder
{
    public function run(): void
    {
        // Map user IDs to roles manually or infer based on conditions
        $users = User::all();

        foreach ($users as $user) {
            // Example logic: infer by customer_id existence
            if ($user->customer_id) {
                $user->assignRole('customer');
            } elseif ($user->email === 'admin@gmail.com') {
                $user->assignRole('admin');
            } elseif ($user->email === 'someprovider@example.com') {
                $user->assignRole('provider');
            } else {
                // fallback if unknown
                $user->assignRole('customer');
            }

            echo "✅ Role assigned to {$user->email}\n";
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mamikhevanshop.id'],
            [
                'name' => 'Admin Mamikhevanshop',
                'password' => Hash::make('Admin#2026'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}

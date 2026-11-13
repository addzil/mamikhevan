<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $administrator = User::firstOrNew(['email' => config('shop.admin.email')]);

        if (! $administrator->exists) {
            $password = config('shop.admin.password');

            if (blank($password)) {
                throw new RuntimeException('Set ADMIN_PASSWORD before creating the initial administrator account.');
            }

            $administrator->fill([
                'name' => 'Admin Mamikhevanshop',
                'password' => Hash::make($password),
                'role' => User::ROLE_ADMINISTRATOR,
            ]);
            $administrator->is_admin = true;
            $administrator->email_verified_at = now();
            $administrator->save();
        }
    }
}

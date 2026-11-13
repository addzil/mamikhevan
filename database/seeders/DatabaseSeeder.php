<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            SizeGuideSeeder::class,
            SetelanSizeGuideSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            ContactMessageSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}

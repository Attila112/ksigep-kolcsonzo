<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            BatterySystemSeeder::class,
            ProductSeeder::class,
            WorkTypeSeeder::class,
            InventorySeeder::class,
            BatteryItemSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\BatterySystem;
use Illuminate\Database\Seeder;

class BatterySystemSeeder extends Seeder
{
    public function run(): void
    {
        $systems = [
            [
                'manufacturer' => 'Makita',
                'name' => 'LXT 18V',
                'voltage' => 18.00,
                'active' => true,
            ],
            [
                'manufacturer' => 'Parkside',
                'name' => 'X20V Team',
                'voltage' => 20.00,
                'active' => true,
            ],
        ];

        foreach ($systems as $system) {
            BatterySystem::query()->updateOrCreate(
                [
                    'manufacturer' => $system['manufacturer'],
                    'name' => $system['name'],
                ],
                $system
            );
        }
    }
}
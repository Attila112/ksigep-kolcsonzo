<?php

namespace Database\Seeders;

use App\Models\BatteryItem;
use App\Models\BatterySystem;
use Illuminate\Database\Seeder;
use RuntimeException;

class BatteryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Makita|LXT 18V' => [
                'battery_count' => 6,
                'charger_count' => 2,
                'battery_prefix' => 'MAK-BAT',
                'charger_prefix' => 'MAK-CHR',
            ],

            'Parkside|X20V Team' => [
                'battery_count' => 4,
                'charger_count' => 2,
                'battery_prefix' => 'PRK-BAT',
                'charger_prefix' => 'PRK-CHR',
            ],
        ];

        foreach ($items as $systemKey => $config) {
            [$manufacturer, $name] = explode('|', $systemKey);

            $system = BatterySystem::query()
                ->where('manufacturer', $manufacturer)
                ->where('name', $name)
                ->first();

            if (! $system) {
                throw new RuntimeException(
                    "A BatterySystem nem található: {$manufacturer} {$name}"
                );
            }

            for ($index = 1; $index <= $config['battery_count']; $index++) {
                BatteryItem::query()->updateOrCreate(
                    [
                        'inventory_code' => sprintf(
                            '%s-%04d',
                            $config['battery_prefix'],
                            $index
                        ),
                    ],
                    [
                        'battery_system_id' => $system->id,
                        'type' => BatteryItem::TYPE_BATTERY,
                        'serial_number' => null,
                        'status' => BatteryItem::STATUS_AVAILABLE,
                        'admin_note' => null,
                    ]
                );
            }

            for ($index = 1; $index <= $config['charger_count']; $index++) {
                BatteryItem::query()->updateOrCreate(
                    [
                        'inventory_code' => sprintf(
                            '%s-%04d',
                            $config['charger_prefix'],
                            $index
                        ),
                    ],
                    [
                        'battery_system_id' => $system->id,
                        'type' => BatteryItem::TYPE_CHARGER,
                        'serial_number' => null,
                        'status' => BatteryItem::STATUS_AVAILABLE,
                        'admin_note' => null,
                    ]
                );
            }
        }
    }
}
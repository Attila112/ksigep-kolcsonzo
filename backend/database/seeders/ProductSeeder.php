<?php

namespace Database\Seeders;

use App\Models\BatterySystem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<int, array<string, mixed>> $products */
        $products = require database_path(
            'development-data/products.php'
        );

        foreach ($products as $productData) {
            $category = Category::query()
                ->where('name', $productData['category'])
                ->first();

            if (! $category) {
                throw new RuntimeException(
                    "A kategória nem található: {$productData['category']}"
                );
            }

            $batterySystemId = null;
            $requiredBatteries = 0;
            $requiredChargers = 0;

            /*
             * Csak akkor keresünk BatterySystem rekordot,
             * ha a termékhez ténylegesen meg van adva
             * akkumulátorrendszer.
             */
            if (isset($productData['battery_system'])) {
                $batterySystemData = $productData['battery_system'];

                $batterySystem = BatterySystem::query()
                    ->where(
                        'manufacturer',
                        $batterySystemData['manufacturer']
                    )
                    ->where(
                        'name',
                        $batterySystemData['name']
                    )
                    ->first();

                if (! $batterySystem) {
                    throw new RuntimeException(
                        'Az akkumulátorrendszer nem található: '
                            . $batterySystemData['manufacturer']
                            . ' '
                            . $batterySystemData['name']
                    );
                }

                $batterySystemId = $batterySystem->id;

                $requiredBatteries = (int) (
                    $productData['required_batteries'] ?? 0
                );

                $requiredChargers = (int) (
                    $productData['required_chargers'] ?? 0
                );
            }

            /*
             * Az SKU a termék stabil belső azonosítója,
             * ezért a seedelés során ez alapján keressük.
             */
            Product::query()->updateOrCreate(
                [
                    'sku' => $productData['sku'],
                ],
                [
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'inventory_prefix' =>
                    $productData['inventory_prefix'],
                    'battery_system_id' => $batterySystemId,
                    'required_batteries' => $requiredBatteries,
                    'required_chargers' => $requiredChargers,
                    'description' => $productData['description'],
                    'price_per_day' =>
                    $productData['price_per_day'],
                    'deposit' => $productData['deposit'],
                    'active' => true,
                ]
            );
        }
    }
}

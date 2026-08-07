<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use RuntimeException;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<int, array<string, mixed>> $products */
        $products = require database_path(
            'development-data/products.php'
        );

        foreach ($products as $productData) {
            $product = Product::query()
                ->where('sku', $productData['sku'])
                ->first();

            if (! $product) {
                throw new RuntimeException(
                    "A termék nem található: {$productData['sku']}"
                );
            }
            if (! $product->inventory_prefix) {
                throw new RuntimeException(
                    "A termékhez nincs inventory prefix: {$product->name}"
                );
            }

            $quantity = (int) $productData['inventory_quantity'];

            for ($index = 1; $index <= $quantity; $index++) {
                $inventoryCode = sprintf(
                    '%s-%03d',
                    $product->inventory_prefix,
                    $index
                );

                InventoryItem::query()->updateOrCreate(
                    [
                        'inventory_code' => $inventoryCode,
                    ],
                    [
                        'product_id' => $product->id,
                        'serial_number' => null,
                        'status' => 'AVAILABLE',
                        'admin_note' => null,
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

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
            /*
             * Az SKU a termék stabil belső azonosítója.
             *
             * Ezért nem a név alapján keressük a meglévő terméket:
             * a termék neve később megváltozhat, az SKU viszont
             * hosszú távon változatlan maradhat.
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

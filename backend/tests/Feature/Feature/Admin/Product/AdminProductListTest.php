<?php

namespace Tests\Feature\Admin\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminProductListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_products(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        Sanctum::actingAs($admin);

        $category = Category::query()->create([
            'name' => 'Kerti gépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Benzines fűnyíró',
            'sku' => 'TEST-FUNYIRO',
            'inventory_prefix' => 'TFU',
            'description' => 'Teszt termék.',
            'price_per_day' => 6000,
            'deposit' => 10000,
            'active' => true,
        ]);

        $response = $this->getJson('/api/admin/products');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath(
                'products.0.id',
                $product->id
            )
            ->assertJsonPath(
                'products.0.name',
                'Benzines fűnyíró'
            )
            ->assertJsonPath(
                'products.0.sku',
                'TEST-FUNYIRO'
            )
            ->assertJsonPath(
                'products.0.category.name',
                'Kerti gépek'
            );
    }

    public function test_admin_product_list_includes_inactive_products(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        Sanctum::actingAs($admin);

        $category = Category::query()->create([
            'name' => 'Kerti gépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Aktív termék',
            'sku' => 'ACTIVE-PRODUCT',
            'inventory_prefix' => 'ACT',
            'description' => 'Teszt.',
            'price_per_day' => 5000,
            'deposit' => 10000,
            'active' => true,
        ]);

        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Inaktív termék',
            'sku' => 'INACTIVE-PRODUCT',
            'inventory_prefix' => 'INA',
            'description' => 'Teszt.',
            'price_per_day' => 5000,
            'deposit' => 10000,
            'active' => false,
        ]);

        $this->getJson('/api/admin/products')
            ->assertOk()
            ->assertJsonCount(2, 'products');
    }

    public function test_customer_cannot_list_admin_products(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/products')
            ->assertForbidden();
    }

    public function test_guest_cannot_list_admin_products(): void
    {
        $this->getJson('/api/admin/products')
            ->assertUnauthorized();
    }
    public function test_admin_product_list_returns_inventory_counts(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        Sanctum::actingAs($admin);

        $category = Category::query()->create([
            'name' => 'Kerti gépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Benzines fűnyíró',
            'sku' => 'TEST-FUNYIRO',
            'inventory_prefix' => 'TFU',
            'description' => 'Teszt termék.',
            'price_per_day' => 6000,
            'deposit' => 10000,
            'active' => true,
        ]);

        \App\Models\InventoryItem::query()->create([
            'product_id' => $product->id,
            'inventory_code' => 'TFU-001',
            'status' => 'AVAILABLE',
        ]);

        \App\Models\InventoryItem::query()->create([
            'product_id' => $product->id,
            'inventory_code' => 'TFU-002',
            'status' => 'RENTED',
        ]);

        \App\Models\InventoryItem::query()->create([
            'product_id' => $product->id,
            'inventory_code' => 'TFU-003',
            'status' => 'MAINTENANCE',
        ]);

        $response = $this->getJson('/api/admin/products');

        $response
            ->assertOk()
            ->assertJsonPath(
                'products.0.inventory_items_count',
                3
            )
            ->assertJsonPath(
                'products.0.available_inventory_count',
                1
            );
    }
}

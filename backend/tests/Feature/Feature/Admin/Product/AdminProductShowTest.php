<?php

namespace Tests\Feature\Admin\Product;

use App\Models\BatterySystem;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_product_details(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $category = Category::factory()->create();

        $batterySystem = BatterySystem::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'battery_system_id' => $batterySystem->id,
            'name' => 'Betonkeverő',
            'sku' => 'BET-001',
        ]);

        InventoryItem::factory()->create([
            'product_id' => $product->id,
            'inventory_code' => 'BET-001',
            'status' => 'AVAILABLE',
        ]);

        InventoryItem::factory()->create([
            'product_id' => $product->id,
            'inventory_code' => 'BET-002',
            'status' => 'RENTED',
        ]);

        $response = $this->getJson(
            "/api/admin/products/{$product->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'product.id',
                $product->id
            )
            ->assertJsonPath(
                'product.name',
                'Betonkeverő'
            )
            ->assertJsonPath(
                'product.sku',
                'BET-001'
            )
            ->assertJsonPath(
                'product.category.id',
                $category->id
            )
            ->assertJsonPath(
                'product.category.name',
                $category->name
            )
            ->assertJsonPath(
                'product.battery_system.id',
                $batterySystem->id
            )
            ->assertJsonPath(
                'product.inventory_items_count',
                2
            )
            ->assertJsonPath(
                'product.available_inventory_count',
                1
            )
            ->assertJsonPath(
                'product.rented_inventory_count',
                1
            )
            ->assertJsonCount(
                2,
                'product.inventory_items'
            );
    }

    public function test_admin_can_view_inactive_product(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $product = Product::factory()->inactive()->create();

        $this->getJson(
            "/api/admin/products/{$product->id}"
        )
            ->assertOk()
            ->assertJsonPath(
                'product.id',
                $product->id
            );
    }

    public function test_customer_cannot_view_admin_product_details(): void
    {
        $customer = User::factory()->customer()->create();

        Sanctum::actingAs($customer);

        $product = Product::factory()->create();

        $this->getJson(
            "/api/admin/products/{$product->id}"
        )
            ->assertForbidden();
    }

    public function test_guest_cannot_view_admin_product_details(): void
    {
        $product = Product::factory()->create();

        $this->getJson(
            "/api/admin/products/{$product->id}"
        )
            ->assertUnauthorized();
    }

    public function test_admin_receives_not_found_for_missing_product(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->getJson(
            "/api/admin/products/999999"
        )
            ->assertNotFound();
    }
}
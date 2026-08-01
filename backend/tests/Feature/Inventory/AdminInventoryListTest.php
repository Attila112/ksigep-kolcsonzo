<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminInventoryListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_inventory_items(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $product = $this->createProduct();

        $inventoryItem = $this->createInventoryItem($product, [
            'inventory_code' => 'BM-001',
            'serial_number' => 'SN-123456',
            'status' => 'MAINTENANCE',
            'admin_note' => 'Ékszíjcsere szükséges.',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/inventory-items');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'inventory_items')
            ->assertJsonPath(
                'inventory_items.0.id',
                $inventoryItem->id
            )
            ->assertJsonPath(
                'inventory_items.0.inventory_code',
                'BM-001'
            )
            ->assertJsonPath(
                'inventory_items.0.serial_number',
                'SN-123456'
            )
            ->assertJsonPath(
                'inventory_items.0.status',
                'MAINTENANCE'
            )
            ->assertJsonPath(
                'inventory_items.0.admin_note',
                'Ékszíjcsere szükséges.'
            )
            ->assertJsonPath(
                'inventory_items.0.product.id',
                $product->id
            )
            ->assertJsonPath(
                'inventory_items.0.product.name',
                $product->name
            );
    }

    public function test_admin_inventory_list_returns_all_machine_statuses(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $product = $this->createProduct();

        $availableItem = $this->createInventoryItem($product, [
            'inventory_code' => 'BM-001',
            'status' => 'AVAILABLE',
        ]);

        $damagedItem = $this->createInventoryItem($product, [
            'inventory_code' => 'BM-002',
            'status' => 'DAMAGED',
        ]);

        $inactiveItem = $this->createInventoryItem($product, [
            'inventory_code' => 'BM-003',
            'status' => 'INACTIVE',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/inventory-items');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'inventory_items');

        $response->assertJsonFragment([
            'id' => $availableItem->id,
            'status' => 'AVAILABLE',
        ]);

        $response->assertJsonFragment([
            'id' => $damagedItem->id,
            'status' => 'DAMAGED',
        ]);

        $response->assertJsonFragment([
            'id' => $inactiveItem->id,
            'status' => 'INACTIVE',
        ]);
    }

    public function test_customer_cannot_get_admin_inventory_items(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/inventory-items')
            ->assertForbidden();
    }

    public function test_guest_cannot_get_admin_inventory_items(): void
    {
        $this->getJson('/api/admin/inventory-items')
            ->assertUnauthorized();
    }

    public function test_admin_inventory_list_returns_empty_array_when_no_items_exist(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/inventory-items')
            ->assertOk()
            ->assertExactJson([
                'inventory_items' => [],
            ]);
    }

    private function createProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Betonkeverők',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Betonkeverő 180L',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ]);
    }

    private function createInventoryItem(
        Product $product,
        array $attributes = []
    ): InventoryItem {
        return InventoryItem::query()->create(array_merge([
            'product_id' => $product->id,
            'inventory_code' => 'BM-001',
            'serial_number' => null,
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ], $attributes));
    }
}

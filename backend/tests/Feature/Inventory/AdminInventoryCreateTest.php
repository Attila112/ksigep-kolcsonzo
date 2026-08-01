<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminInventoryCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_inventory_item(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $product = $this->createProduct();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/inventory-items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-002',
            'serial_number' => 'SN-987654',
            'status' => 'AVAILABLE',
            'admin_note' => 'Új gép.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'message',
                'A géppéldány sikeresen létrejött.'
            )
            ->assertJsonPath(
                'inventory_item.product_id',
                $product->id
            )
            ->assertJsonPath(
                'inventory_item.inventory_code',
                'BM-002'
            )
            ->assertJsonPath(
                'inventory_item.serial_number',
                'SN-987654'
            )
            ->assertJsonPath(
                'inventory_item.status',
                'AVAILABLE'
            );

        $this->assertDatabaseHas('inventory_items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-002',
            'serial_number' => 'SN-987654',
            'status' => 'AVAILABLE',
            'admin_note' => 'Új gép.',
        ]);
    }

    public function test_customer_cannot_create_inventory_item(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        $product = $this->createProduct();

        Sanctum::actingAs($customer);

        $this->postJson('/api/admin/inventory-items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-002',
            'status' => 'AVAILABLE',
        ])->assertForbidden();

        $this->assertDatabaseCount('inventory_items', 0);
    }

    public function test_guest_cannot_create_inventory_item(): void
    {
        $product = $this->createProduct();

        $this->postJson('/api/admin/inventory-items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-002',
            'status' => 'AVAILABLE',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('inventory_items', 0);
    }

    public function test_product_must_exist(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/inventory-items', [
            'product_id' => 999999,
            'inventory_code' => 'BM-002',
            'status' => 'AVAILABLE',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_id');

        $this->assertDatabaseCount('inventory_items', 0);
    }

    public function test_inventory_code_must_be_unique(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $product = $this->createProduct();

        $product->inventoryItems()->create([
            'inventory_code' => 'BM-001',
            'serial_number' => null,
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/inventory-items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-001',
            'status' => 'AVAILABLE',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('inventory_code');

        $this->assertDatabaseCount('inventory_items', 1);
    }

    public function test_status_must_be_valid(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $product = $this->createProduct();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/inventory-items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-002',
            'status' => 'BROKEN',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseCount('inventory_items', 0);
    }

    public function test_serial_number_must_be_unique_when_provided(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $product = $this->createProduct();

        $product->inventoryItems()->create([
            'inventory_code' => 'BM-001',
            'serial_number' => 'SN-123456',
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/inventory-items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-002',
            'serial_number' => 'SN-123456',
            'status' => 'AVAILABLE',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('serial_number');

        $this->assertDatabaseCount('inventory_items', 1);
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
    public function test_admin_can_create_inventory_item_with_inspection_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $product = $this->createProduct();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/inventory-items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-INSPECTION',
            'serial_number' => null,
            'status' => 'INSPECTION',
            'admin_note' => 'Visszavétel utáni ellenőrzés.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'inventory_item.status',
                'INSPECTION'
            );

        $this->assertDatabaseHas('inventory_items', [
            'product_id' => $product->id,
            'inventory_code' => 'BM-INSPECTION',
            'status' => 'INSPECTION',
        ]);
    }
}

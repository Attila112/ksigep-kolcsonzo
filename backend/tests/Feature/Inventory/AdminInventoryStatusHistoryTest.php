<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminInventoryStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_inventory_item_status_history(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem();

        InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => null,
            'from_status' => 'AVAILABLE',
            'to_status' => 'RENTED',
            'note' => 'Automatikus státuszváltás gépkiadáskor.',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $manualHistory = InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => $admin->id,
            'from_status' => 'INSPECTION',
            'to_status' => 'AVAILABLE',
            'note' => 'A gép megfelelő állapotú.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status-history"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'inventory_item.id',
                $inventoryItem->id
            )
            ->assertJsonPath(
                'inventory_item.inventory_code',
                $inventoryItem->inventory_code
            )
            ->assertJsonCount(2, 'status_history')
            ->assertJsonPath(
                'status_history.0.id',
                $manualHistory->id
            )
            ->assertJsonPath(
                'status_history.0.from_status',
                'INSPECTION'
            )
            ->assertJsonPath(
                'status_history.0.to_status',
                'AVAILABLE'
            )
            ->assertJsonPath(
                'status_history.0.changed_by.id',
                $admin->id
            )
            ->assertJsonPath(
                'status_history.0.changed_by.name',
                $admin->name
            );
    }

    public function test_automatic_status_change_has_null_changed_by(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem();

        InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => null,
            'from_status' => 'AVAILABLE',
            'to_status' => 'RENTED',
            'note' => 'Automatikus státuszváltás gépkiadáskor.',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status-history"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'status_history.0.changed_by',
                null
            );
    }

    public function test_status_history_is_ordered_by_latest_first(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem();

        $olderHistory = InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => null,
            'from_status' => 'AVAILABLE',
            'to_status' => 'RENTED',
            'note' => null,
        ]);

        $olderHistory
            ->forceFill([
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ])
            ->saveQuietly();

        $newerHistory = InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => $admin->id,
            'from_status' => 'INSPECTION',
            'to_status' => 'AVAILABLE',
            'note' => 'Ellenőrizve.',
        ]);

        $newerHistory
            ->forceFill([
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->saveQuietly();

        Sanctum::actingAs($admin);

        $response = $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status-history"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'status_history.0.id',
                $newerHistory->id
            )
            ->assertJsonPath(
                'status_history.1.id',
                $olderHistory->id
            );
    }

    public function test_admin_gets_empty_history_when_no_status_changes_exist(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem();

        Sanctum::actingAs($admin);

        $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status-history"
        )
            ->assertOk()
            ->assertJsonPath(
                'inventory_item.id',
                $inventoryItem->id
            )
            ->assertJsonCount(0, 'status_history');
    }

    public function test_customer_cannot_get_inventory_status_history(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        $inventoryItem = $this->createInventoryItem();

        Sanctum::actingAs($customer);

        $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status-history"
        )->assertForbidden();
    }

    public function test_guest_cannot_get_inventory_status_history(): void
    {
        $inventoryItem = $this->createInventoryItem();

        $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status-history"
        )->assertUnauthorized();
    }

    public function test_missing_inventory_item_returns_not_found(): void
    {
        $admin = $this->createAdmin();

        Sanctum::actingAs($admin);

        $this->getJson(
            '/api/admin/inventory-items/999999/status-history'
        )->assertNotFound();
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);
    }

    private function createInventoryItem(): InventoryItem
    {
        $category = Category::query()->create([
            'name' => 'Kisgépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Betonkeverő 180L',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ]);

        return InventoryItem::query()->create([
            'product_id' => $product->id,
            'inventory_code' => 'BM-' . uniqid(),
            'serial_number' => null,
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ]);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryStatusHistoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_belongs_to_inventory_item(): void
    {
        $inventoryItem = $this->createInventoryItem();

        $history = InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => null,
            'from_status' => 'AVAILABLE',
            'to_status' => 'MAINTENANCE',
            'note' => 'Időszakos szerviz.',
        ]);

        $this->assertTrue(
            $history->inventoryItem->is($inventoryItem)
        );
    }

    public function test_history_can_belong_to_user(): void
    {
        $inventoryItem = $this->createInventoryItem();
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $history = InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => $admin->id,
            'from_status' => 'INSPECTION',
            'to_status' => 'AVAILABLE',
            'note' => 'A gép megfelelő állapotú.',
        ]);

        $this->assertTrue(
            $history->changedBy->is($admin)
        );
    }

    public function test_changed_by_user_can_be_null(): void
    {
        $inventoryItem = $this->createInventoryItem();

        $history = InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => null,
            'from_status' => 'AVAILABLE',
            'to_status' => 'RENTED',
            'note' => 'Automatikus kiadás.',
        ]);

        $this->assertNull($history->changedBy);
    }

    public function test_inventory_item_has_many_status_histories(): void
    {
        $inventoryItem = $this->createInventoryItem();

        InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => null,
            'from_status' => 'AVAILABLE',
            'to_status' => 'RENTED',
            'note' => null,
        ]);

        InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => null,
            'from_status' => 'RENTED',
            'to_status' => 'INSPECTION',
            'note' => null,
        ]);

        $this->assertCount(
            2,
            $inventoryItem->statusHistories
        );
    }

    public function test_user_has_many_inventory_status_changes(): void
    {
        $inventoryItem = $this->createInventoryItem();

        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        InventoryStatusHistory::query()->create([
            'inventory_item_id' => $inventoryItem->id,
            'changed_by_user_id' => $admin->id,
            'from_status' => 'INSPECTION',
            'to_status' => 'AVAILABLE',
            'note' => 'Ellenőrizve.',
        ]);

        $this->assertCount(
            1,
            $admin->inventoryStatusChanges
        );
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

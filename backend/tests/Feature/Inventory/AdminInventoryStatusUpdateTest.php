<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminInventoryStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mark_inspected_machine_as_available(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem([
            'status' => 'INSPECTION',
            'admin_note' => 'Visszavétel utáni ellenőrzés.',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'AVAILABLE',
                'admin_note' => 'A gép megfelelő állapotú.',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'message',
                'A gép állapota sikeresen módosítva.'
            )
            ->assertJsonPath(
                'inventory_item.status',
                'AVAILABLE'
            )
            ->assertJsonPath(
                'inventory_item.admin_note',
                'A gép megfelelő állapotú.'
            );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
            'admin_note' => 'A gép megfelelő állapotú.',
        ]);
    }

    public function test_admin_can_move_available_machine_to_maintenance(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem([
            'status' => 'AVAILABLE',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'MAINTENANCE',
                'admin_note' => 'Időszakos szerviz szükséges.',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'inventory_item.status',
                'MAINTENANCE'
            );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'MAINTENANCE',
            'admin_note' => 'Időszakos szerviz szükséges.',
        ]);
    }

    public function test_admin_can_mark_machine_as_damaged(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem([
            'status' => 'INSPECTION',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'DAMAGED',
                'admin_note' => 'A burkolat sérült.',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'inventory_item.status',
                'DAMAGED'
            );
    }

    public function test_admin_can_mark_machine_as_inactive(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem([
            'status' => 'AVAILABLE',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'INACTIVE',
                'admin_note' => 'A gép ideiglenesen kivonva.',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'inventory_item.status',
                'INACTIVE'
            );
    }

    public function test_rented_machine_status_cannot_be_changed_manually(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem([
            'status' => 'RENTED',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'AVAILABLE',
                'admin_note' => 'Teszt módosítás.',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Kiadott gép állapota csak a visszavételi folyamatban módosítható.'
            );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'RENTED',
        ]);
    }

    public function test_admin_cannot_set_status_to_rented_manually(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem([
            'status' => 'AVAILABLE',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'RENTED',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
        ]);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem();

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'UNKNOWN',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_admin_note_is_required_for_non_available_status(): void
    {
        $admin = $this->createAdmin();

        $inventoryItem = $this->createInventoryItem([
            'status' => 'AVAILABLE',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'MAINTENANCE',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('admin_note');
    }

    public function test_customer_cannot_update_inventory_status(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        $inventoryItem = $this->createInventoryItem();

        Sanctum::actingAs($customer);

        $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'MAINTENANCE',
                'admin_note' => 'Teszt.',
            ]
        )->assertForbidden();
    }

    public function test_guest_cannot_update_inventory_status(): void
    {
        $inventoryItem = $this->createInventoryItem();

        $this->patchJson(
            "/api/admin/inventory-items/{$inventoryItem->id}/status",
            [
                'status' => 'MAINTENANCE',
                'admin_note' => 'Teszt.',
            ]
        )->assertUnauthorized();
    }

    public function test_missing_inventory_item_returns_not_found(): void
    {
        $admin = $this->createAdmin();

        Sanctum::actingAs($admin);

        $this->patchJson(
            '/api/admin/inventory-items/999999/status',
            [
                'status' => 'AVAILABLE',
            ]
        )->assertNotFound();
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);
    }

    private function createInventoryItem(
        array $attributes = []
    ): InventoryItem {
        $category = Category::query()->create([
            'name' => 'Kisgépek-' . uniqid(),
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

        return InventoryItem::query()->create(array_merge([
            'product_id' => $product->id,
            'inventory_code' => 'BM-' . uniqid(),
            'serial_number' => null,
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ], $attributes));
    }
}

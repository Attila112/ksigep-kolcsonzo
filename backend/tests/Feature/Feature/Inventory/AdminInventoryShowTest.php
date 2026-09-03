<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\BatterySystem;

class AdminInventoryShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_inventory_item_details(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $category = Category::factory()->create([
            'name' => 'Kerti gépek',
        ]);
        $batterySystem = BatterySystem::factory()
            ->makita()
            ->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 2,
            'required_chargers' => 1,
            'name' => 'Akkumulátoros fúró-csavarozó',
            'sku' => 'FURO-CSAVAROZO-AKKU',
            'inventory_prefix' => 'AFU',
        ]);

        $inventoryItem = InventoryItem::factory()->create([
            'product_id' => $product->id,
            'inventory_code' => 'AFU-001',
            'serial_number' => 'SN-123456',
            'status' => 'MAINTENANCE',
            'admin_note' => 'Tokmany csere',
        ]);

        $response = $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'inventory_item.id',
                $inventoryItem->id
            )
            ->assertJsonPath(
                'inventory_item.inventory_code',
                'AFU-001'
            )
            ->assertJsonPath(
                'inventory_item.serial_number',
                'SN-123456'
            )
            ->assertJsonPath(
                'inventory_item.status',
                'MAINTENANCE'
            )
            ->assertJsonPath(
                'inventory_item.admin_note',
                'Tokmany csere'
            )
            ->assertJsonPath(
                'inventory_item.product.id',
                $product->id
            )
            ->assertJsonPath(
                'inventory_item.product.name',
                'Akkumulátoros fúró-csavarozó'
            )
            ->assertJsonPath(
                'inventory_item.product.sku',
                'FURO-CSAVAROZO-AKKU'
            )
            ->assertJsonPath(
                'inventory_item.product.category.id',
                $category->id
            )
            ->assertJsonPath(
                'inventory_item.product.category.name',
                'Kerti gépek'
            )
            ->assertJsonPath(
                'inventory_item.product.battery_system.id',
                $batterySystem->id
            )
            ->assertJsonPath(
                'inventory_item.product.battery_system.manufacturer',
                'Makita'
            )
            ->assertJsonPath(
                'inventory_item.product.battery_system.name',
                'LXT 18V'
            )
            ->assertJsonPath(
                'inventory_item.product.required_batteries',
                2
            )
            ->assertJsonPath(
                'inventory_item.product.required_chargers',
                1
            );
    }

    public function test_admin_can_view_inactive_inventory_item(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $inventoryItem = InventoryItem::factory()
            ->inactive()
            ->create();

        $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}"
        )
            ->assertOk()
            ->assertJsonPath(
                'inventory_item.id',
                $inventoryItem->id
            )
            ->assertJsonPath(
                'inventory_item.status',
                'INACTIVE'
            );
    }

    public function test_customer_cannot_view_admin_inventory_item_details(): void
    {
        $customer = User::factory()
            ->customer()
            ->create();

        Sanctum::actingAs($customer);

        $inventoryItem = InventoryItem::factory()->create();

        $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}"
        )
            ->assertForbidden();
    }

    public function test_guest_cannot_view_admin_inventory_item_details(): void
    {
        $inventoryItem = InventoryItem::factory()->create();

        $this->getJson(
            "/api/admin/inventory-items/{$inventoryItem->id}"
        )
            ->assertUnauthorized();
    }

    public function test_admin_receives_not_found_for_missing_inventory_item(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $this->getJson(
            '/api/admin/inventory-items/999999'
        )
            ->assertNotFound();
    }
}

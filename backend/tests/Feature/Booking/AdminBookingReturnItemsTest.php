<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\BookingItemAllocation;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\BatteryItem;
use App\Models\BatterySystem;
use App\Models\BookingItemAllocationBatteryItem;

class AdminBookingReturnItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_partially_return_inventory_items(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $firstItem = $this->createInventoryItem(
            $product,
            'BM-001',
            ['status' => 'RENTED']
        );

        $secondItem = $this->createInventoryItem(
            $product,
            'BM-002',
            ['status' => 'RENTED']
        );

        $booking = $this->createActiveBooking();

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product, quantity: 2)
        );

        $firstAllocation = $this->createAllocation(
            $bookingItem->id,
            $firstItem->id
        );

        $secondAllocation = $this->createAllocation(
            $bookingItem->id,
            $secondItem->id
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $firstItem->id,
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'message',
                'A kiválasztott gépek sikeresen visszavételre kerültek.'
            )
            ->assertJsonPath('booking.status', 'ACTIVE');

        $this->assertDatabaseHas('inventory_items', [
            'id' => $firstItem->id,
            'status' => 'INSPECTION',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $secondItem->id,
            'status' => 'RENTED',
        ]);

        $this->assertDatabaseMissing('booking_item_allocations', [
            'id' => $firstAllocation->id,
            'returned_at' => null,
        ]);

        $this->assertDatabaseHas('booking_item_allocations', [
            'id' => $secondAllocation->id,
            'returned_at' => null,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_booking_becomes_completed_when_every_item_is_returned(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $firstItem = $this->createInventoryItem(
            $product,
            'BM-001',
            ['status' => 'RENTED']
        );

        $secondItem = $this->createInventoryItem(
            $product,
            'BM-002',
            ['status' => 'RENTED']
        );

        $booking = $this->createActiveBooking();

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product, quantity: 2)
        );

        $this->createAllocation(
            $bookingItem->id,
            $firstItem->id
        );

        $this->createAllocation(
            $bookingItem->id,
            $secondItem->id
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $firstItem->id,
                    $secondItem->id,
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('booking.status', 'COMPLETED');

        $this->assertDatabaseHas('inventory_items', [
            'id' => $firstItem->id,
            'status' => 'INSPECTION',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $secondItem->id,
            'status' => 'INSPECTION',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'COMPLETED',
        ]);

        $this->assertDatabaseMissing('booking_item_allocations', [
            'booking_item_id' => $bookingItem->id,
            'returned_at' => null,
        ]);
    }

    public function test_only_active_booking_can_accept_returns(): void
    {
        $admin = $this->createAdmin();

        $product = $this->createProduct();
        $inventoryItem = $this->createInventoryItem(
            $product,
            'BM-001',
            ['status' => 'RENTED']
        );

        $booking = Booking::query()->create([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'status' => 'CONFIRMED',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Csak aktív foglaláshoz vehető vissza gép.'
            );
    }

    public function test_only_items_allocated_to_booking_can_be_returned(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $allocatedItem = $this->createInventoryItem(
            $product,
            'BM-001',
            ['status' => 'RENTED']
        );

        $otherItem = $this->createInventoryItem(
            $product,
            'BM-002',
            ['status' => 'RENTED']
        );

        $booking = $this->createActiveBooking();

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        $this->createAllocation(
            $bookingItem->id,
            $allocatedItem->id
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $otherItem->id,
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A kiválasztott gépek egyike nem tartozik ehhez a foglaláshoz.'
            );
    }

    public function test_already_returned_item_cannot_be_returned_again(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $inventoryItem = $this->createInventoryItem(
            $product,
            'BM-001',
            ['status' => 'INSPECTION']
        );

        $booking = $this->createActiveBooking();

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A kiválasztott gépek egyike már visszavételre került.'
            );
    }

    public function test_inventory_item_ids_are_required(): void
    {
        $admin = $this->createAdmin();
        $booking = $this->createActiveBooking();

        Sanctum::actingAs($admin);

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            []
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('inventory_item_ids');
    }

    public function test_customer_cannot_return_inventory_items(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        $booking = $this->createActiveBooking();

        Sanctum::actingAs($customer);

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [1],
            ]
        )->assertForbidden();
    }

    public function test_guest_cannot_return_inventory_items(): void
    {
        $booking = $this->createActiveBooking();

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [1],
            ]
        )->assertUnauthorized();
    }

    public function test_missing_booking_returns_not_found(): void
    {
        $admin = $this->createAdmin();

        Sanctum::actingAs($admin);

        $this->postJson(
            '/api/admin/bookings/999999/return-items',
            [
                'inventory_item_ids' => [1],
            ]
        )->assertNotFound();
    }
    public function test_returning_machine_also_returns_assigned_battery_items(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => 18,
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus fúrógép',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 1,
            'required_chargers' => 1,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'RET-AF-001',
            [
                'status' => 'RENTED',
            ]
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'RET-BAT-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $charger = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'RET-CHG-001',
            'type' => BatteryItem::TYPE_CHARGER,
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $booking = $this->createBooking([
            'status' => 'ACTIVE',
        ]);

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        $machineAllocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        BookingItemAllocationBatteryItem::query()->create([
            'booking_item_allocation_id' => $machineAllocation->id,
            'battery_item_id' => $battery->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        BookingItemAllocationBatteryItem::query()->create([
            'booking_item_allocation_id' => $machineAllocation->id,
            'battery_item_id' => $charger->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
            ]
        );

        $response->assertOk();

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_INSPECTION,
        ]);

        $this->assertDatabaseHas('battery_items', [
            'id' => $charger->id,
            'status' => BatteryItem::STATUS_INSPECTION,
        ]);

        $this->assertDatabaseHas('battery_status_histories', [
            'battery_item_id' => $battery->id,
            'from_status' => BatteryItem::STATUS_RENTED,
            'to_status' => BatteryItem::STATUS_INSPECTION,
        ]);

        $this->assertDatabaseHas('battery_status_histories', [
            'battery_item_id' => $charger->id,
            'from_status' => BatteryItem::STATUS_RENTED,
            'to_status' => BatteryItem::STATUS_INSPECTION,
        ]);
    }


    public function test_returning_machine_marks_its_battery_allocations_as_returned(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => 18,
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus csavarbehajtó',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 1,
            'required_chargers' => 0,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'RET-AC-001',
            [
                'status' => 'RENTED',
            ]
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'RET-BAT-002',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $booking = $this->createBooking([
            'status' => 'ACTIVE',
        ]);

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        $machineAllocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        $batteryAllocation =
            BookingItemAllocationBatteryItem::query()->create([
                'booking_item_allocation_id' => $machineAllocation->id,
                'battery_item_id' => $battery->id,
                'assigned_at' => now()->subDay(),
                'returned_at' => null,
            ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
            ]
        );

        $response->assertOk();

        $batteryAllocation->refresh();

        $this->assertNotNull(
            $batteryAllocation->returned_at
        );
    }


    public function test_partial_return_only_returns_battery_items_assigned_to_returned_machine(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => 18,
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus fúrógép',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 1,
            'required_chargers' => 0,
        ]);

        $inventoryItemOne = $this->createInventoryItem(
            $product,
            'RET-PART-001',
            [
                'status' => 'RENTED',
            ]
        );

        $inventoryItemTwo = $this->createInventoryItem(
            $product,
            'RET-PART-002',
            [
                'status' => 'RENTED',
            ]
        );

        $batteryOne = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'RET-BAT-PART-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $batteryTwo = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'RET-BAT-PART-002',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $booking = $this->createBooking([
            'status' => 'ACTIVE',
        ]);

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product, 2)
        );

        $allocationOne = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItemOne->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        $allocationTwo = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItemTwo->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        $batteryAllocationOne =
            BookingItemAllocationBatteryItem::query()->create([
                'booking_item_allocation_id' => $allocationOne->id,
                'battery_item_id' => $batteryOne->id,
                'assigned_at' => now()->subDay(),
                'returned_at' => null,
            ]);

        $batteryAllocationTwo =
            BookingItemAllocationBatteryItem::query()->create([
                'booking_item_allocation_id' => $allocationTwo->id,
                'battery_item_id' => $batteryTwo->id,
                'assigned_at' => now()->subDay(),
                'returned_at' => null,
            ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItemOne->id,
                ],
            ]
        );

        $response->assertOk();

        $batteryAllocationOne->refresh();
        $batteryAllocationTwo->refresh();

        $this->assertNotNull(
            $batteryAllocationOne->returned_at
        );

        $this->assertNull(
            $batteryAllocationTwo->returned_at
        );

        $this->assertDatabaseHas('battery_items', [
            'id' => $batteryOne->id,
            'status' => BatteryItem::STATUS_INSPECTION,
        ]);

        $this->assertDatabaseHas('battery_items', [
            'id' => $batteryTwo->id,
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'ACTIVE',
        ]);
    }


    public function test_completed_booking_returns_all_assigned_battery_items(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => 18,
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus sarokcsiszoló',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 1,
            'required_chargers' => 0,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'RET-COMPLETE-001',
            [
                'status' => 'RENTED',
            ]
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'RET-BAT-COMPLETE-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $booking = $this->createBooking([
            'status' => 'ACTIVE',
        ]);

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        $machineAllocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        BookingItemAllocationBatteryItem::query()->create([
            'booking_item_allocation_id' => $machineAllocation->id,
            'battery_item_id' => $battery->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
            ]
        );

        $response->assertOk();

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_INSPECTION,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'COMPLETED',
        ]);
    }

    public function test_returning_machine_without_battery_allocations_still_works(): void
    {
        $admin = $this->createAdmin();

        $product = $this->createProduct([
            'name' => 'Betonkeverő',
            'battery_system_id' => null,
            'required_batteries' => 0,
            'required_chargers' => 0,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'RET-NOBAT-001',
            [
                'status' => 'RENTED',
            ]
        );

        $booking = $this->createBooking([
            'status' => 'ACTIVE',
        ]);

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
            ]
        );

        $response->assertOk();

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'INSPECTION',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'COMPLETED',
        ]);
    }


    public function test_already_returned_battery_allocation_is_not_processed_again(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => 18,
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus fúrógép',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 1,
            'required_chargers' => 0,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'RET-ALREADY-001',
            [
                'status' => 'RENTED',
            ]
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'RET-BAT-ALREADY-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_INSPECTION,
        ]);

        $booking = $this->createBooking([
            'status' => 'ACTIVE',
        ]);

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        $machineAllocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        BookingItemAllocationBatteryItem::query()->create([
            'booking_item_allocation_id' => $machineAllocation->id,
            'battery_item_id' => $battery->id,
            'assigned_at' => now()->subDays(2),
            'returned_at' => now()->subDay(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
            ]
        );

        $response->assertOk();

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_INSPECTION,
        ]);

        $this->assertDatabaseCount(
            'battery_status_histories',
            0
        );
    }


    public function test_battery_return_creates_exactly_one_status_history_entry(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => 18,
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus csavarbehajtó',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 1,
            'required_chargers' => 0,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'RET-HIST-001',
            [
                'status' => 'RENTED',
            ]
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'RET-BAT-HIST-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $booking = $this->createBooking([
            'status' => 'ACTIVE',
        ]);

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        $machineAllocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        BookingItemAllocationBatteryItem::query()->create([
            'booking_item_allocation_id' => $machineAllocation->id,
            'battery_item_id' => $battery->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/return-items",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
            ]
        );

        $response->assertOk();

        $this->assertDatabaseHas('battery_status_histories', [
            'battery_item_id' => $battery->id,
            'from_status' => BatteryItem::STATUS_RENTED,
            'to_status' => BatteryItem::STATUS_INSPECTION,
        ]);

        $this->assertDatabaseCount(
            'battery_status_histories',
            1
        );
    }
    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);
    }

    private function createActiveBooking(): Booking
    {
        return Booking::query()->create([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'status' => 'ACTIVE',
        ]);
    }

    private function createProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Kisgépek',
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
        string $inventoryCode,
        array $attributes = []
    ): InventoryItem {
        return InventoryItem::query()->create(array_merge([
            'product_id' => $product->id,
            'inventory_code' => $inventoryCode,
            'serial_number' => null,
            'status' => 'RENTED',
            'admin_note' => null,
        ], $attributes));
    }

    private function createAllocation(
        int $bookingItemId,
        int $inventoryItemId
    ): BookingItemAllocation {
        return BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItemId,
            'inventory_item_id' => $inventoryItemId,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);
    }

    private function bookingItemData(
        Product $product,
        int $quantity = 1
    ): array {
        return [
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => $quantity,
            'price_per_day' => $product->price_per_day,
            'deposit_per_item' => $product->deposit,
            'rental_days' => 3,
            'rental_subtotal' =>
            3 * (float) $product->price_per_day * $quantity,
            'deposit_subtotal' =>
            (float) $product->deposit * $quantity,
        ];
    }
    private function createBooking(array $attributes = []): Booking
    {
        return Booking::query()->create(array_merge([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'status' => 'ACTIVE',
        ], $attributes));
    }
}

<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\BatteryItem;
use App\Models\BatterySystem;

class AdminBookingIssueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_issue_confirmed_booking_with_selected_inventory_items(): void
    {
        $admin = $this->createAdmin();

        $concreteMixer = $this->createProduct([
            'name' => 'Betonkeverő 180L',
        ]);

        $drill = $this->createProduct([
            'name' => 'Fúrógép',
        ]);

        $mixerOne = $this->createInventoryItem(
            $concreteMixer,
            'BM-001'
        );

        $mixerTwo = $this->createInventoryItem(
            $concreteMixer,
            'BM-002'
        );

        $drillOne = $this->createInventoryItem(
            $drill,
            'FG-001'
        );

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $mixerBookingItem = $booking->items()->create(
            $this->bookingItemData(
                product: $concreteMixer,
                quantity: 2
            )
        );

        $drillBookingItem = $booking->items()->create(
            $this->bookingItemData(
                product: $drill,
                quantity: 1
            )
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $mixerOne->id,
                    $mixerTwo->id,
                    $drillOne->id,
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'message',
                'A foglalás gépei sikeresen kiadásra kerültek.'
            )
            ->assertJsonPath('booking.id', $booking->id)
            ->assertJsonPath('booking.status', 'ACTIVE');

        $this->assertDatabaseHas('booking_item_allocations', [
            'booking_item_id' => $mixerBookingItem->id,
            'inventory_item_id' => $mixerOne->id,
        ]);

        $this->assertDatabaseHas('booking_item_allocations', [
            'booking_item_id' => $mixerBookingItem->id,
            'inventory_item_id' => $mixerTwo->id,
        ]);

        $this->assertDatabaseHas('booking_item_allocations', [
            'booking_item_id' => $drillBookingItem->id,
            'inventory_item_id' => $drillOne->id,
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $mixerOne->id,
            'status' => 'RENTED',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $mixerTwo->id,
            'status' => 'RENTED',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $drillOne->id,
            'status' => 'RENTED',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_only_confirmed_booking_can_be_issued(): void
    {
        $admin = $this->createAdmin();

        $product = $this->createProduct();
        $inventoryItem = $this->createInventoryItem(
            $product,
            'BM-001'
        );

        $booking = $this->createBooking([
            'status' => 'PENDING',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
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
                'Csak jóváhagyott foglalás adható ki.'
            );

        $this->assertDatabaseCount(
            'booking_item_allocations',
            0
        );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
        ]);
    }

    public function test_exact_required_inventory_quantity_must_be_selected(): void
    {
        $admin = $this->createAdmin();

        $product = $this->createProduct();

        $inventoryItem = $this->createInventoryItem(
            $product,
            'BM-001'
        );

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData(
                product: $product,
                quantity: 2
            )
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
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
                'A kiválasztott gépek száma nem egyezik a foglalásban szereplő mennyiséggel.'
            );

        $this->assertDatabaseCount(
            'booking_item_allocations',
            0
        );
    }

    public function test_selected_inventory_item_must_belong_to_booked_product(): void
    {
        $admin = $this->createAdmin();

        $bookedProduct = $this->createProduct([
            'name' => 'Betonkeverő',
        ]);

        $otherProduct = $this->createProduct([
            'name' => 'Fúrógép',
        ]);

        $wrongInventoryItem = $this->createInventoryItem(
            $otherProduct,
            'FG-001'
        );

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($bookedProduct)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $wrongInventoryItem->id,
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A kiválasztott gépek egyike nem a foglalásban szereplő termékhez tartozik.'
            );

        $this->assertDatabaseCount(
            'booking_item_allocations',
            0
        );
    }

    public function test_only_available_inventory_item_can_be_issued(): void
    {
        $admin = $this->createAdmin();

        $product = $this->createProduct();

        $inventoryItem = $this->createInventoryItem(
            product: $product,
            inventoryCode: 'BM-001',
            attributes: [
                'status' => 'MAINTENANCE',
            ],
        );

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
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
                'Csak elérhető állapotú gép adható ki.'
            );

        $this->assertDatabaseCount(
            'booking_item_allocations',
            0
        );
    }

    public function test_same_inventory_item_cannot_be_selected_twice(): void
    {
        $admin = $this->createAdmin();

        $product = $this->createProduct();

        $inventoryItem = $this->createInventoryItem(
            $product,
            'BM-001'
        );

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData(
                product: $product,
                quantity: 2
            )
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                    $inventoryItem->id,
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'inventory_item_ids.0'
            );

        $this->assertDatabaseCount(
            'booking_item_allocations',
            0
        );
    }

    public function test_inventory_item_ids_are_required(): void
    {
        $admin = $this->createAdmin();
        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        Sanctum::actingAs($admin);

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            []
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'inventory_item_ids'
            );
    }

    public function test_customer_cannot_issue_booking(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        Sanctum::actingAs($customer);

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [1],
            ]
        )->assertForbidden();
    }

    public function test_guest_cannot_issue_booking(): void
    {
        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
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
            '/api/admin/bookings/999999/issue',
            [
                'inventory_item_ids' => [1],
            ]
        )->assertNotFound();
    }
    public function test_admin_can_issue_battery_powered_machine_with_required_batteries_and_charger(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => 18,
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus fűkasza',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 2,
            'required_chargers' => 1,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'AF-001'
        );

        $batteryOne = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $batteryTwo = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-002',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $charger = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'CHG-001',
            'type' => BatteryItem::TYPE_CHARGER,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
                'battery_allocations' => [
                    [
                        'inventory_item_id' => $inventoryItem->id,
                        'battery_item_ids' => [
                            $batteryOne->id,
                            $batteryTwo->id,
                            $charger->id,
                        ],
                    ],
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'booking.status',
                'ACTIVE'
            );

        $this->assertDatabaseHas(
            'booking_item_allocations',
            [
                'booking_item_id' => $bookingItem->id,
                'inventory_item_id' => $inventoryItem->id,
            ]
        );

        $allocationId = \App\Models\BookingItemAllocation::query()
            ->where('booking_item_id', $bookingItem->id)
            ->where('inventory_item_id', $inventoryItem->id)
            ->value('id');

        $this->assertNotNull($allocationId);

        foreach (
            [
                $batteryOne,
                $batteryTwo,
                $charger,
            ] as $batteryItem
        ) {
            $this->assertDatabaseHas(
                'booking_item_allocation_battery_items',
                [
                    'booking_item_allocation_id' => $allocationId,
                    'battery_item_id' => $batteryItem->id,
                ]
            );

            $this->assertDatabaseHas('battery_items', [
                'id' => $batteryItem->id,
                'status' => BatteryItem::STATUS_RENTED,
            ]);

            $this->assertDatabaseHas(
                'battery_status_histories',
                [
                    'battery_item_id' => $batteryItem->id,
                    'from_status' => BatteryItem::STATUS_AVAILABLE,
                    'to_status' => BatteryItem::STATUS_RENTED,
                ]
            );
        }
    }
    public function test_only_available_battery_item_can_be_issued(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => '18V',
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
            'AF-001'
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_MAINTENANCE,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
                'battery_allocations' => [
                    [
                        'inventory_item_id' => $inventoryItem->id,
                        'battery_item_ids' => [
                            $battery->id,
                        ],
                    ],
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Csak elérhető állapotú akkumulátor vagy töltő adható ki.'
            );

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_MAINTENANCE,
        ]);

        $this->assertDatabaseCount(
            'booking_item_allocation_battery_items',
            0
        );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);
    }
    public function test_battery_item_must_belong_to_products_battery_system(): void
    {
        $admin = $this->createAdmin();

        $makitaSystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => '18V',
            'active' => true,
        ]);

        $parksideSystem = BatterySystem::query()->create([
            'name' => 'Parkside X20V Team',
            'manufacturer' => 'Parkside',
            'voltage' => '20V',
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus fúrógép',
            'battery_system_id' => $makitaSystem->id,
            'required_batteries' => 1,
            'required_chargers' => 0,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'AF-001'
        );

        $wrongBattery = BatteryItem::factory()->create([
            'battery_system_id' => $parksideSystem->id,
            'inventory_code' => 'BAT-PARK-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
                'battery_allocations' => [
                    [
                        'inventory_item_id' => $inventoryItem->id,
                        'battery_item_ids' => [
                            $wrongBattery->id,
                        ],
                    ],
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A kiválasztott akkumulátor vagy töltő nem kompatibilis a gép akkumulátorrendszerével.'
            );

        $this->assertDatabaseHas('battery_items', [
            'id' => $wrongBattery->id,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $this->assertDatabaseCount(
            'booking_item_allocation_battery_items',
            0
        );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);
    }
    public function test_battery_powered_machine_requires_exact_number_of_batteries(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => '18V',
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus fűkasza',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 2,
            'required_chargers' => 0,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'AFK-001'
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
                'battery_allocations' => [
                    [
                        'inventory_item_id' => $inventoryItem->id,
                        'battery_item_ids' => [
                            $battery->id,
                        ],
                    ],
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A kiválasztott akkumulátorok száma nem egyezik a géphez szükséges mennyiséggel.'
            );

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $this->assertDatabaseCount(
            'booking_item_allocation_battery_items',
            0
        );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);
    }
    public function test_battery_powered_machine_requires_exact_number_of_chargers(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => '18V',
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
            'AF-001'
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
                'battery_allocations' => [
                    [
                        'inventory_item_id' => $inventoryItem->id,
                        'battery_item_ids' => [
                            $battery->id,
                        ],
                    ],
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A kiválasztott töltők száma nem egyezik a géphez szükséges mennyiséggel.'
            );

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $this->assertDatabaseCount(
            'booking_item_allocation_battery_items',
            0
        );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);
    }
    public function test_battery_powered_machine_cannot_be_issued_without_battery_allocation(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => '18V',
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
            'AF-001'
        );

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
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
                'Az akkumulátoros géphez ki kell választani a szükséges akkumulátorokat és töltőket.'
            );

        $this->assertDatabaseCount(
            'booking_item_allocation_battery_items',
            0
        );

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);
    }
    public function test_same_battery_item_cannot_be_assigned_to_multiple_machines(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => '18V',
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
            'AF-DUP-001'
        );

        $inventoryItemTwo = $this->createInventoryItem(
            $product,
            'AF-DUP-002'
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-DUP-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product, 2)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $inventoryItemOne->id,
                    $inventoryItemTwo->id,
                ],
                'battery_allocations' => [
                    [
                        'inventory_item_id' => $inventoryItemOne->id,
                        'battery_item_ids' => [
                            $battery->id,
                        ],
                    ],
                    [
                        'inventory_item_id' => $inventoryItemTwo->id,
                        'battery_item_ids' => [
                            $battery->id,
                        ],
                    ],
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Ugyanaz az akkumulátor vagy töltő nem rendelhető egyszerre több géphez.'
            );

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItemOne->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItemTwo->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);

        $this->assertDatabaseCount(
            'booking_item_allocation_battery_items',
            0
        );
    }


    public function test_battery_allocation_cannot_reference_unselected_inventory_item(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => '18V',
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Akkus csavarbehajtó',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 1,
            'required_chargers' => 0,
        ]);

        $selectedInventoryItem = $this->createInventoryItem(
            $product,
            'AC-SELECTED-001'
        );

        $unselectedInventoryItem = $this->createInventoryItem(
            $product,
            'AC-UNSELECTED-001'
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-UNSELECTED-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $selectedInventoryItem->id,
                ],
                'battery_allocations' => [
                    [
                        'inventory_item_id' => $unselectedInventoryItem->id,
                        'battery_item_ids' => [
                            $battery->id,
                        ],
                    ],
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Akkumulátor vagy töltő csak a kiadáshoz kiválasztott géphez rendelhető.'
            );

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $selectedInventoryItem->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);

        $this->assertDatabaseCount(
            'booking_item_allocation_battery_items',
            0
        );
    }


    public function test_non_battery_machine_cannot_receive_battery_allocation(): void
    {
        $admin = $this->createAdmin();

        $batterySystem = BatterySystem::query()->create([
            'name' => 'Makita LXT',
            'manufacturer' => 'Makita',
            'voltage' => '18V',
            'active' => true,
        ]);

        $product = $this->createProduct([
            'name' => 'Betonkeverő',
            'battery_system_id' => null,
            'required_batteries' => 0,
            'required_chargers' => 0,
        ]);

        $inventoryItem = $this->createInventoryItem(
            $product,
            'BET-001'
        );

        $battery = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-NONBAT-001',
            'type' => BatteryItem::TYPE_BATTERY,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/issue",
            [
                'inventory_item_ids' => [
                    $inventoryItem->id,
                ],
                'battery_allocations' => [
                    [
                        'inventory_item_id' => $inventoryItem->id,
                        'battery_item_ids' => [
                            $battery->id,
                        ],
                    ],
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Ehhez a géphez nem rendelhető akkumulátor vagy töltő.'
            );

        $this->assertDatabaseHas('battery_items', [
            'id' => $battery->id,
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItem->id,
            'status' => 'AVAILABLE',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);

        $this->assertDatabaseCount(
            'booking_item_allocation_battery_items',
            0
        );
    }
    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);
    }

    private function createBooking(
        array $attributes = []
    ): Booking {
        return Booking::query()->create(array_merge([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'status' => 'CONFIRMED',
        ], $attributes));
    }

    private function createProduct(
        array $attributes = []
    ): Product {
        $category = Category::query()->create([
            'name' => 'Kisgépek-' . uniqid(),
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'name' => 'Betonkeverő 180L',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ], $attributes));
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
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ], $attributes));
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
}

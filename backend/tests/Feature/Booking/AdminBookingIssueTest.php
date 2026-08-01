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

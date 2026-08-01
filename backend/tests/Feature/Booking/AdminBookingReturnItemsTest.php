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
}

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

class AdminBookingApproveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_booking(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $this->createInventoryItems($product, 2);

        $booking = $this->createBooking([
            'status' => 'PENDING',
        ]);

        $booking->items()->create(
            $this->bookingItemData($product, quantity: 2)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/approve"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'message',
                'A foglalás sikeresen jóváhagyva.'
            )
            ->assertJsonPath('booking.id', $booking->id)
            ->assertJsonPath('booking.status', 'CONFIRMED');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);
    }

    public function test_approval_does_not_assign_concrete_inventory_items(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $this->createInventoryItems($product, 1);

        $booking = $this->createBooking();

        $bookingItem = $booking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($admin);

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/approve"
        )->assertOk();

        $this->assertDatabaseHas('booking_items', [
            'id' => $bookingItem->id,
            'inventory_item_id' => null,
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'product_id' => $product->id,
            'status' => 'AVAILABLE',
        ]);
    }

    public function test_booking_cannot_be_approved_when_quantity_is_no_longer_available(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $this->createInventoryItems($product, 1);

        $bookingToApprove = $this->createBooking([
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'status' => 'PENDING',
        ]);

        $bookingToApprove->items()->create(
            $this->bookingItemData($product, quantity: 1)
        );

        $otherBooking = $this->createBooking([
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'status' => 'CONFIRMED',
            'customer_email' => 'masik@example.com',
        ]);

        $otherBooking->items()->create(
            $this->bookingItemData($product, quantity: 1)
        );

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$bookingToApprove->id}/approve"
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A(z) Betonkeverő 180L termékből nincs elegendő szabad darab a kiválasztott időszakban.'
            );

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingToApprove->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_only_pending_booking_can_be_approved(): void
    {
        $admin = $this->createAdmin();

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/approve"
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Csak függőben lévő foglalás hagyható jóvá.'
            );

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);
    }

    public function test_customer_cannot_approve_booking(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        $booking = $this->createBooking();

        Sanctum::actingAs($customer);

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/approve"
        )->assertForbidden();
    }

    public function test_guest_cannot_approve_booking(): void
    {
        $booking = $this->createBooking();

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/approve"
        )->assertUnauthorized();
    }

    public function test_missing_booking_returns_not_found(): void
    {
        $admin = $this->createAdmin();

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/bookings/999999/approve')
            ->assertNotFound();
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);
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
            'status' => 'PENDING',
        ], $attributes));
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

    private function createInventoryItems(
        Product $product,
        int $quantity
    ): void {
        for ($index = 1; $index <= $quantity; $index++) {
            InventoryItem::query()->create([
                'product_id' => $product->id,
                'inventory_code' => sprintf('BM-%03d', $index),
                'serial_number' => null,
                'status' => 'AVAILABLE',
                'admin_note' => null,
            ]);
        }
    }

    private function bookingItemData(
        Product $product,
        int $quantity = 1
    ): array {
        return [
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => $quantity,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000 * $quantity,
            'deposit_subtotal' => 30000 * $quantity,
        ];
    }
}

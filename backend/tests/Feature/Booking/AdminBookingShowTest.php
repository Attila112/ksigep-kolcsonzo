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

class AdminBookingShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_booking_details(): void
    {
        $admin = $this->createAdmin();
        $customer = User::factory()->create();

        $product = $this->createProduct();

        $inventoryItem = $this->createInventoryItem(
            product: $product,
            inventoryCode: 'BM-001',
            status: 'RENTED',
        );

        $booking = $this->createBooking($customer, [
            'status' => 'ACTIVE',
            'customer_note' => 'Reggel érkeznék.',
            'admin_note' => 'Személyi igazolvány ellenőrizve.',
        ]);

        $bookingItem = $booking->items()->create([
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 1,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000,
            'deposit_subtotal' => 30000,
        ]);

        $allocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDay(),
            'returned_at' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson(
            "/api/admin/bookings/{$booking->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('booking.id', $booking->id)
            ->assertJsonPath('booking.user_id', $customer->id)
            ->assertJsonPath('booking.customer_name', $customer->name)
            ->assertJsonPath('booking.customer_email', $customer->email)
            ->assertJsonPath('booking.status', 'ACTIVE')
            ->assertJsonPath('booking.pickup_type', 'SELF_PICKUP')
            ->assertJsonPath('booking.customer_note', 'Reggel érkeznék.')
            ->assertJsonPath(
                'booking.admin_note',
                'Személyi igazolvány ellenőrizve.'
            )
            ->assertJsonPath('booking.rental_total', 24000)
            ->assertJsonPath('booking.deposit_total', 30000)
            ->assertJsonPath('booking.total_payable', 54000)
            ->assertJsonPath(
                'booking.items.0.product.id',
                $product->id
            )
            ->assertJsonPath(
                'booking.items.0.product.name',
                $product->name
            )
            ->assertJsonPath('booking.items.0.quantity', 1)
            ->assertJsonPath(
                'booking.items.0.allocations.0.id',
                $allocation->id
            )
            ->assertJsonPath(
                'booking.items.0.allocations.0.inventory_item.id',
                $inventoryItem->id
            )
            ->assertJsonPath(
                'booking.items.0.allocations.0.inventory_item.inventory_code',
                'BM-001'
            )
            ->assertJsonPath(
                'booking.items.0.allocations.0.inventory_item.status',
                'RENTED'
            )
            ->assertJsonPath(
                'booking.items.0.allocations.0.returned_at',
                null
            );
    }

    public function test_booking_details_show_returned_inventory_items(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $inventoryItem = $this->createInventoryItem(
            product: $product,
            inventoryCode: 'BM-001',
            status: 'INSPECTION',
        );

        $booking = $this->createBooking(null, [
            'status' => 'COMPLETED',
        ]);

        $bookingItem = $booking->items()->create([
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 1,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000,
            'deposit_subtotal' => 30000,
        ]);

        BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now()->subDays(3),
            'returned_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson(
            "/api/admin/bookings/{$booking->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('booking.status', 'COMPLETED')
            ->assertJsonPath(
                'booking.items.0.allocations.0.inventory_item.status',
                'INSPECTION'
            );

        $this->assertNotNull(
            $response->json(
                'booking.items.0.allocations.0.returned_at'
            )
        );
    }

    public function test_guest_booking_details_have_null_user(): void
    {
        $admin = $this->createAdmin();

        $booking = $this->createBooking(null, [
            'user_id' => null,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/admin/bookings/{$booking->id}")
            ->assertOk()
            ->assertJsonPath('booking.user', null);
    }

    public function test_customer_cannot_get_admin_booking_details(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        $booking = $this->createBooking($customer);

        Sanctum::actingAs($customer);

        $this->getJson("/api/admin/bookings/{$booking->id}")
            ->assertForbidden();
    }

    public function test_guest_cannot_get_admin_booking_details(): void
    {
        $booking = $this->createBooking();

        $this->getJson("/api/admin/bookings/{$booking->id}")
            ->assertUnauthorized();
    }

    public function test_missing_booking_returns_not_found(): void
    {
        $admin = $this->createAdmin();

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/bookings/999999')
            ->assertNotFound();
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);
    }

    private function createBooking(
        ?User $user = null,
        array $attributes = []
    ): Booking {
        return Booking::query()->create(array_merge([
            'user_id' => $user?->id,
            'customer_name' => $user?->name ?? 'Teszt Elek',
            'customer_email' => $user?->email ?? 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'status' => 'PENDING',
            'customer_note' => null,
            'admin_note' => null,
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

    private function createInventoryItem(
        Product $product,
        string $inventoryCode,
        string $status = 'AVAILABLE',
    ): InventoryItem {
        return InventoryItem::query()->create([
            'product_id' => $product->id,
            'inventory_code' => $inventoryCode,
            'serial_number' => null,
            'status' => $status,
            'admin_note' => null,
        ]);
    }
}

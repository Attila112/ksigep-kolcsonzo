<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBookingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_all_bookings(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $customer = User::factory()->create();

        $product = $this->createProduct();

        $booking = $this->createBooking($customer);

        $booking->items()->create([
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 2,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 48000,
            'deposit_subtotal' => 60000,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/bookings');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'bookings')
            ->assertJsonPath('bookings.0.id', $booking->id)
            ->assertJsonPath('bookings.0.customer_name', $customer->name)
            ->assertJsonPath('bookings.0.status', 'PENDING')
            ->assertJsonPath('bookings.0.items.0.product.id', $product->id)
            ->assertJsonPath('bookings.0.items.0.quantity', 2)
            ->assertJsonPath('bookings.0.rental_total', 48000)
            ->assertJsonPath('bookings.0.deposit_total', 60000)
            ->assertJsonPath('bookings.0.total_payable', 108000);
    }

    public function test_admin_bookings_are_ordered_by_latest_first(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        $customer = User::factory()->create();

        $olderBooking = $this->createBooking($customer);
        $olderBooking
            ->forceFill([
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ])
            ->saveQuietly();

        $newerBooking = $this->createBooking($customer);
        $newerBooking
            ->forceFill([
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->saveQuietly();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/bookings');

        $response
            ->assertOk()
            ->assertJsonPath('bookings.0.id', $newerBooking->id)
            ->assertJsonPath('bookings.1.id', $olderBooking->id);
    }

    public function test_customer_cannot_get_admin_bookings(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/bookings')
            ->assertForbidden();
    }

    public function test_guest_cannot_get_admin_bookings(): void
    {
        $this->getJson('/api/admin/bookings')
            ->assertUnauthorized();
    }

    public function test_admin_gets_empty_array_when_no_bookings_exist(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/bookings')
            ->assertOk()
            ->assertExactJson([
                'bookings' => [],
            ]);
    }

    private function createBooking(
        User $user,
        array $attributes = []
    ): Booking {
        return Booking::query()->create(array_merge([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '+36301234567',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => now()
                ->addDays(10)
                ->setTime(9, 0),
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
}

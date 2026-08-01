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

class MyBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_own_bookings(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $product = $this->createProduct();
        $this->createInventoryItem($product);

        $ownBooking = $this->createBooking($user, [
            'customer_email' => $user->email,
        ]);

        $ownBooking->items()->create(
            $this->bookingItemData($product)
        );

        $otherBooking = $this->createBooking($otherUser, [
            'customer_email' => $otherUser->email,
        ]);

        $otherBooking->items()->create(
            $this->bookingItemData($product)
        );

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/my-bookings');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'bookings')
            ->assertJsonPath('bookings.0.id', $ownBooking->id)
            ->assertJsonPath('bookings.0.user_id', $user->id)
            ->assertJsonPath(
                'bookings.0.items.0.product.id',
                $product->id
            );

        $response->assertJsonMissing([
            'id' => $otherBooking->id,
        ]);
    }

    public function test_my_bookings_are_ordered_by_latest_first(): void
    {
        $user = User::factory()->create();

        $olderBooking = $this->createBooking($user);
        $olderBooking
            ->forceFill([
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ])
            ->saveQuietly();

        $newerBooking = $this->createBooking($user);
        $newerBooking
            ->forceFill([
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->saveQuietly();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/my-bookings');

        $response
            ->assertOk()
            ->assertJsonPath('bookings.0.id', $newerBooking->id)
            ->assertJsonPath('bookings.1.id', $olderBooking->id);
    }

    public function test_authenticated_user_without_bookings_gets_empty_array(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/my-bookings')
            ->assertOk()
            ->assertExactJson([
                'bookings' => [],
            ]);
    }

    public function test_guest_cannot_get_my_bookings(): void
    {
        $this->getJson('/api/my-bookings')
            ->assertUnauthorized();
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

    private function createInventoryItem(
        Product $product
    ): InventoryItem {
        return InventoryItem::query()->create([
            'product_id' => $product->id,
            'inventory_code' => 'BM-001',
            'serial_number' => null,
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ]);
    }

    private function bookingItemData(Product $product): array
    {
        return [
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 1,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000,
            'deposit_subtotal' => 30000,
        ];
    }
}

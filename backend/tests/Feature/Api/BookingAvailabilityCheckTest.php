<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_available_quantity_for_multiple_products(): void
    {
        $category = Category::factory()->create([
            'active' => true,
        ]);

        $firstProduct = Product::factory()->create([
            'category_id' => $category->id,
            'active' => true,
        ]);

        $secondProduct = Product::factory()->create([
            'category_id' => $category->id,
            'active' => true,
        ]);

        InventoryItem::factory()->count(2)->create([
            'product_id' => $firstProduct->id,
            'status' => 'AVAILABLE',
        ]);

        InventoryItem::factory()->create([
            'product_id' => $secondProduct->id,
            'status' => 'AVAILABLE',
        ]);

        $response = $this->postJson(
            '/api/booking/availability',
            [
                'start_date' => '2026-10-10',
                'end_date' => '2026-10-12',
                'items' => [
                    [
                        'product_id' =>
                            $firstProduct->id,
                    ],
                    [
                        'product_id' =>
                            $secondProduct->id,
                    ],
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertExactJson([
                'start_date' => '2026-10-10',
                'end_date' => '2026-10-12',
                'items' => [
                    [
                        'product_id' =>
                            $firstProduct->id,
                        'available_quantity' => 2,
                        'available' => true,
                    ],
                    [
                        'product_id' =>
                            $secondProduct->id,
                        'available_quantity' => 1,
                        'available' => true,
                    ],
                ],
            ]);
    }

    public function test_it_subtracts_overlapping_bookings_from_available_quantity(): void
    {
        $category = Category::factory()->create([
            'active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'active' => true,
        ]);

        InventoryItem::factory()->count(2)->create([
            'product_id' => $product->id,
            'status' => 'AVAILABLE',
        ]);

        $booking = Booking::query()->create([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+3612345678',
            'start_date' => '2026-10-11',
            'end_date' => '2026-10-13',
            'pickup_type' => 'SELF_PICKUP',
            'status' => 'CONFIRMED',
        ]);

        BookingItem::query()->create([
            'booking_id' => $booking->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000,
            'deposit_subtotal' => 30000,
        ]);

        $response = $this->postJson(
            '/api/booking/availability',
            [
                'start_date' => '2026-10-10',
                'end_date' => '2026-10-12',
                'items' => [
                    [
                        'product_id' =>
                            $product->id,
                    ],
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'items.0.available_quantity',
                1
            )
            ->assertJsonPath(
                'items.0.available',
                true
            );
    }

    public function test_it_returns_zero_when_product_is_fully_reserved(): void
    {
        $category = Category::factory()->create([
            'active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'active' => true,
        ]);

        InventoryItem::factory()->create([
            'product_id' => $product->id,
            'status' => 'AVAILABLE',
        ]);

        $booking = Booking::query()->create([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+3612345678',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12',
            'pickup_type' => 'SELF_PICKUP',
            'status' => 'PENDING',
        ]);

        BookingItem::query()->create([
            'booking_id' => $booking->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000,
            'deposit_subtotal' => 30000,
        ]);

        $response = $this->postJson(
            '/api/booking/availability',
            [
                'start_date' => '2026-10-10',
                'end_date' => '2026-10-12',
                'items' => [
                    [
                        'product_id' =>
                            $product->id,
                    ],
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'items.0.available_quantity',
                0
            )
            ->assertJsonPath(
                'items.0.available',
                false
            );
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson(
            '/api/booking/availability',
            []
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'start_date',
                'end_date',
                'items',
            ]);
    }

    public function test_it_validates_that_end_date_is_not_before_start_date(): void
    {
        $category = Category::factory()->create([
            'active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'active' => true,
        ]);

        $response = $this->postJson(
            '/api/booking/availability',
            [
                'start_date' => '2026-10-12',
                'end_date' => '2026-10-10',
                'items' => [
                    [
                        'product_id' =>
                            $product->id,
                    ],
                ],
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'end_date',
            ]);
    }

    public function test_it_rejects_inactive_products(): void
    {
        $category = Category::factory()->create([
            'active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'active' => false,
        ]);

        $response = $this->postJson(
            '/api/booking/availability',
            [
                'start_date' => '2026-10-10',
                'end_date' => '2026-10-12',
                'items' => [
                    [
                        'product_id' =>
                            $product->id,
                    ],
                ],
            ]
        );

        $response->assertUnprocessable();
    }
}
<?php

namespace Tests\Feature\Product;

use App\Models\Booking;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAvailabilityCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_daily_availability_calendar_for_an_active_product(): void
    {
        $product = $this->createProduct();

        $this->createInventoryItems(
            product: $product,
            quantity: 3,
        );

        $booking = $this->createBooking([
            'start_date' => '2026-10-03',
            'end_date' => '2026-10-04',
            'status' => 'CONFIRMED',
        ]);

        $booking->items()->create([
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 2,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 2,
            'rental_subtotal' => 32000,
            'deposit_subtotal' => 60000,
        ]);

        $response = $this->getJson(
            "/api/products/{$product->id}/availability-calendar?start_date=2026-10-01&end_date=2026-10-05"
        );

        $response
            ->assertOk()
            ->assertJson([
                'product_id' => $product->id,
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-05',
                'days' => [
                    [
                        'date' => '2026-10-01',
                        'available_quantity' => 3,
                        'available' => true,
                    ],
                    [
                        'date' => '2026-10-02',
                        'available_quantity' => 3,
                        'available' => true,
                    ],
                    [
                        'date' => '2026-10-03',
                        'available_quantity' => 1,
                        'available' => true,
                    ],
                    [
                        'date' => '2026-10-04',
                        'available_quantity' => 1,
                        'available' => true,
                    ],
                    [
                        'date' => '2026-10-05',
                        'available_quantity' => 3,
                        'available' => true,
                    ],
                ],
            ]);
    }

    public function test_it_rejects_invalid_calendar_date_range(): void
    {
        $product = $this->createProduct();

        $response = $this->getJson(
            "/api/products/{$product->id}/availability-calendar?start_date=2026-10-10&end_date=2026-10-01"
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'end_date',
            ]);
    }

    public function test_it_requires_calendar_start_and_end_date(): void
    {
        $product = $this->createProduct();

        $response = $this->getJson(
            "/api/products/{$product->id}/availability-calendar"
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'start_date',
                'end_date',
            ]);
    }

    public function test_it_returns_404_for_inactive_product(): void
    {
        $product = $this->createProduct([
            'active' => false,
        ]);

        $response = $this->getJson(
            "/api/products/{$product->id}/availability-calendar?start_date=2026-10-01&end_date=2026-10-05"
        );

        $response->assertNotFound();
    }

    public function test_it_returns_404_when_product_category_is_inactive(): void
    {
        $product = $this->createProduct();

        $product->category()->update([
            'active' => false,
        ]);

        $response = $this->getJson(
            "/api/products/{$product->id}/availability-calendar?start_date=2026-10-01&end_date=2026-10-05"
        );

        $response->assertNotFound();
    }

    private function createProduct(
        array $attributes = []
    ): Product {
        $category = Category::query()->create([
            'name' => 'Betonkeverők',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        return Product::query()->create(
            array_merge([
                'category_id' => $category->id,
                'name' => 'Betonkeverő 180L',
                'description' => 'Teszt termék',
                'price_per_day' => 8000,
                'deposit' => 30000,
                'active' => true,
            ], $attributes)
        );
    }

    private function createInventoryItems(
        Product $product,
        int $quantity
    ): void {
        for (
            $index = 1;
            $index <= $quantity;
            $index++
        ) {
            InventoryItem::query()->create([
                'product_id' => $product->id,
                'inventory_code' => sprintf(
                    'BM-%03d',
                    $index
                ),
                'serial_number' => null,
                'status' => 'AVAILABLE',
                'admin_note' => null,
            ]);
        }
    }

    private function createBooking(
        array $attributes = []
    ): Booking {
        return Booking::query()->create(
            array_merge([
                'customer_name' => 'Teszt Elek',
                'customer_email' => 'teszt@example.com',
                'customer_phone' => '+36301234567',
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-05',
                'pickup_type' => 'SELF_PICKUP',
                'planned_pickup_at' => '2026-10-01 09:00:00',
                'status' => 'PENDING',
            ], $attributes)
        );
    }
}
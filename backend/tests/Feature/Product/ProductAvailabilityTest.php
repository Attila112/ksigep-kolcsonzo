<?php

namespace Tests\Feature\Product;

use App\Models\Booking;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_available_quantity_for_product(): void
    {
        $product = $this->createProduct();

        $this->createInventoryItems($product, 3);

        $response = $this->getJson(
            "/api/products/{$product->id}/availability"
            . '?start_date=2026-08-10'
            . '&end_date=2026-08-12'
        );

        $response
            ->assertOk()
            ->assertJsonPath('product_id', $product->id)
            ->assertJsonPath('start_date', '2026-08-10')
            ->assertJsonPath('end_date', '2026-08-12')
            ->assertJsonPath('available_quantity', 3)
            ->assertJsonPath('available', true);
    }

    public function test_overlapping_booking_reduces_available_quantity(): void
    {
        $product = $this->createProduct();

        $this->createInventoryItems($product, 3);

        $booking = $this->createBooking([
            'start_date' => '2026-08-11',
            'end_date' => '2026-08-13',
            'status' => 'CONFIRMED',
        ]);

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

        $response = $this->getJson(
            "/api/products/{$product->id}/availability"
            . '?start_date=2026-08-10'
            . '&end_date=2026-08-12'
        );

        $response
            ->assertOk()
            ->assertJsonPath('available_quantity', 1)
            ->assertJsonPath('available', true);
    }

    public function test_it_returns_unavailable_when_no_quantity_remains(): void
    {
        $product = $this->createProduct();

        $this->createInventoryItems($product, 1);

        $booking = $this->createBooking([
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'status' => 'PENDING',
        ]);

        $booking->items()->create([
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 1,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000,
            'deposit_subtotal' => 30000,
        ]);

        $response = $this->getJson(
            "/api/products/{$product->id}/availability"
            . '?start_date=2026-08-10'
            . '&end_date=2026-08-12'
        );

        $response
            ->assertOk()
            ->assertJsonPath('available_quantity', 0)
            ->assertJsonPath('available', false);
    }

    public function test_start_date_is_required(): void
    {
        $product = $this->createProduct();

        $this->getJson(
            "/api/products/{$product->id}/availability"
            . '?end_date=2026-08-12'
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('start_date');
    }

    public function test_end_date_is_required(): void
    {
        $product = $this->createProduct();

        $this->getJson(
            "/api/products/{$product->id}/availability"
            . '?start_date=2026-08-10'
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end_date');
    }

    public function test_end_date_cannot_be_before_start_date(): void
    {
        $product = $this->createProduct();

        $this->getJson(
            "/api/products/{$product->id}/availability"
            . '?start_date=2026-08-15'
            . '&end_date=2026-08-10'
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end_date');
    }

    public function test_inactive_product_is_not_publicly_available(): void
    {
        $product = $this->createProduct([
            'active' => false,
        ]);

        $this->getJson(
            "/api/products/{$product->id}/availability"
            . '?start_date=2026-08-10'
            . '&end_date=2026-08-12'
        )->assertNotFound();
    }

    public function test_product_from_inactive_category_is_not_publicly_available(): void
    {
        $category = $this->createCategory([
            'active' => false,
        ]);

        $product = $this->createProduct(
            attributes: [],
            category: $category,
        );

        $this->getJson(
            "/api/products/{$product->id}/availability"
            . '?start_date=2026-08-10'
            . '&end_date=2026-08-12'
        )->assertNotFound();
    }

    public function test_missing_product_returns_not_found(): void
    {
        $this->getJson(
            '/api/products/999999/availability'
            . '?start_date=2026-08-10'
            . '&end_date=2026-08-12'
        )->assertNotFound();
    }

    private function createCategory(array $attributes = []): Category
    {
        return Category::query()->create(array_merge([
            'name' => 'Kisgépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ], $attributes));
    }

    private function createProduct(
        array $attributes = [],
        ?Category $category = null,
    ): Product {
        $category ??= $this->createCategory();

        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'name' => 'Betonkeverő 180L',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ], $attributes));
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
}
<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Services\BookingAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_available_inventory_quantity(): void
    {
        $product = $this->createProduct();

        $this->createInventoryItems($product, 3);

        $service = new BookingAvailabilityService();

        $availableQuantity = $service->availableQuantity(
            product: $product,
            startDate: '2026-08-10',
            endDate: '2026-08-12',
        );

        $this->assertSame(3, $availableQuantity);
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

        $service = new BookingAvailabilityService();

        $availableQuantity = $service->availableQuantity(
            product: $product,
            startDate: '2026-08-10',
            endDate: '2026-08-12',
        );

        $this->assertSame(1, $availableQuantity);
    }

    public function test_cancelled_booking_does_not_reduce_available_quantity(): void
    {
        $product = $this->createProduct();

        $this->createInventoryItems($product, 3);

        $booking = $this->createBooking([
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'status' => 'CANCELLED',
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

        $service = new BookingAvailabilityService();

        $availableQuantity = $service->availableQuantity(
            product: $product,
            startDate: '2026-08-10',
            endDate: '2026-08-12',
        );

        $this->assertSame(3, $availableQuantity);
    }

    public function test_unavailable_inventory_items_are_not_counted(): void
    {
        $product = $this->createProduct();

        $this->createInventoryItems($product, 2);

        InventoryItem::query()->create([
            'product_id' => $product->id,
            'inventory_code' => 'BM-MAINTENANCE',
            'serial_number' => null,
            'status' => 'MAINTENANCE',
            'admin_note' => null,
        ]);

        $service = new BookingAvailabilityService();

        $availableQuantity = $service->availableQuantity(
            product: $product,
            startDate: '2026-08-10',
            endDate: '2026-08-12',
        );

        $this->assertSame(2, $availableQuantity);
    }

    private function createProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Betonkeverők',
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

    private function createInventoryItems(Product $product, int $quantity): void
    {
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

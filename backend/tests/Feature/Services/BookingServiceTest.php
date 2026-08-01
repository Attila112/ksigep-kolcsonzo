<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use App\Services\BookingService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_guest_booking_with_multiple_products(): void
    {
        $category = $this->createCategory();

        $concreteMixer = $this->createProduct($category, [
            'name' => 'Betonkeverő 180L',
            'price_per_day' => 8000,
            'deposit' => 30000,
        ]);

        $drill = $this->createProduct($category, [
            'name' => 'Fúrógép',
            'price_per_day' => 5000,
            'deposit' => 10000,
        ]);

        $this->createInventoryItems($concreteMixer, 2, 'BM');
        $this->createInventoryItems($drill, 1, 'FG');

        $service = app(BookingService::class);

        $booking = $service->create(
            data: $this->bookingData([
                'items' => [
                    [
                        'product_id' => $concreteMixer->id,
                        'quantity' => 2,
                    ],
                    [
                        'product_id' => $drill->id,
                        'quantity' => 1,
                    ],
                ],
            ]),
            user: null,
        );

        $booking->load('items');

        $this->assertNull($booking->user_id);
        $this->assertSame('PENDING', $booking->status);
        $this->assertCount(2, $booking->items);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'user_id' => null,
            'customer_email' => 'teszt@example.com',
            'status' => 'PENDING',
        ]);

        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $booking->id,
            'product_id' => $concreteMixer->id,
            'quantity' => 2,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 48000,
            'deposit_subtotal' => 60000,
        ]);

        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $booking->id,
            'product_id' => $drill->id,
            'quantity' => 1,
            'price_per_day' => 5000,
            'deposit_per_item' => 10000,
            'rental_days' => 3,
            'rental_subtotal' => 15000,
            'deposit_subtotal' => 10000,
        ]);
    }

    public function test_it_assigns_authenticated_user_to_booking(): void
    {
        $user = User::factory()->create();

        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 1, 'BM');

        $service = app(BookingService::class);

        $booking = $service->create(
            data: $this->bookingData([
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]),
            user: $user,
        );

        $this->assertSame($user->id, $booking->user_id);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'user_id' => $user->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_it_rejects_booking_when_requested_quantity_is_unavailable(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 1, 'BM');

        $service = app(BookingService::class);

        try {
            $service->create(
                data: $this->bookingData([
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'quantity' => 2,
                        ],
                    ],
                ]),
                user: null,
            );

            $this->fail(
                'A foglalásnak hibát kellett volna dobnia elégtelen készlet esetén.'
            );
        } catch (DomainException $exception) {
            $this->assertSame(
                'A(z) Betonkeverő 180L termékből csak 1 darab érhető el a kiválasztott időszakban.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('booking_items', 0);
    }

    private function bookingData(array $attributes = []): array
    {
        return array_merge([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'customer_note' => 'Reggel érkeznék.',
            'items' => [],
        ], $attributes);
    }

    private function createCategory(): Category
    {
        return Category::query()->create([
            'name' => 'Kisgépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);
    }

    private function createProduct(
        Category $category,
        array $attributes = []
    ): Product {
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
        int $quantity,
        string $prefix
    ): void {
        for ($index = 1; $index <= $quantity; $index++) {
            InventoryItem::query()->create([
                'product_id' => $product->id,
                'inventory_code' => sprintf(
                    '%s-%03d',
                    $prefix,
                    $index
                ),
                'serial_number' => null,
                'status' => 'AVAILABLE',
                'admin_note' => null,
            ]);
        }
    }
    public function test_it_rejects_booking_with_invalid_date_range(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 1, 'BM');

        $service = app(BookingService::class);

        $this->expectException(\InvalidArgumentException::class);

        $service->create(
            data: $this->bookingData([
                'start_date' => '2026-08-15',
                'end_date' => '2026-08-10',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]),
            user: null,
        );
    }

    public function test_it_rejects_inactive_product(): void
    {
        $category = $this->createCategory();

        $product = $this->createProduct($category, [
            'active' => false,
        ]);

        $this->createInventoryItems($product, 1, 'BM');

        $service = app(BookingService::class);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $service->create(
            data: $this->bookingData([
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]),
            user: null,
        );
    }

    public function test_overlapping_pending_booking_reduces_availability(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 2, 'BM');

        $existingBooking = $this->createBooking([
            'start_date' => '2026-08-11',
            'end_date' => '2026-08-13',
            'status' => 'PENDING',
        ]);

        $existingBooking->items()->create([
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 1,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000,
            'deposit_subtotal' => 30000,
        ]);

        $service = app(BookingService::class);

        $this->expectException(DomainException::class);

        $service->create(
            data: $this->bookingData([
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ]),
            user: null,
        );
    }
    private function createBooking(array $attributes = []): \App\Models\Booking
    {
        return \App\Models\Booking::query()->create(array_merge([
            'customer_name' => 'Korábbi Tesztelő',
            'customer_email' => 'korabbi@example.com',
            'customer_phone' => '+36301111111',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'status' => 'PENDING',
        ], $attributes));
    }
}

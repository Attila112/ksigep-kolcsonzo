<?php

namespace Tests\Feature\Booking;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_booking_with_multiple_products(): void
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

        $response = $this->postJson('/api/bookings', [
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => now()
                ->addDays(10)
                ->setTime(9, 0)
                ->toDateTimeString(),
            'customer_note' => 'Reggel érkeznék.',
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
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'message',
                'A foglalási kérelmet sikeresen elküldted.'
            )
            ->assertJsonPath('booking.user_id', null)
            ->assertJsonPath('booking.status', 'PENDING')
            ->assertJsonCount(2, 'booking.items')
            ->assertJsonPath('booking.rental_total', 63000)
            ->assertJsonPath('booking.deposit_total', 70000)
            ->assertJsonPath('booking.total_payable', 133000);

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('booking_items', 2);
    }

    public function test_authenticated_user_is_assigned_to_booking(): void
    {
        $user = User::factory()->create([
            'active' => true,
        ]);

        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 1, 'BM');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/bookings', [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '+36301234567',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => now()
                ->addDays(10)
                ->setTime(9, 0)
                ->toDateTimeString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('booking.user_id', $user->id)
            ->assertJsonPath('booking.status', 'PENDING');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_booking_is_rejected_when_requested_quantity_is_unavailable(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 1, 'BM');

        $response = $this->postJson('/api/bookings', [
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => now()
                ->addDays(10)
                ->setTime(9, 0)
                ->toDateTimeString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A(z) Betonkeverő 180L termékből csak 1 darab érhető el a kiválasztott időszakban.'
            );

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('booking_items', 0);
    }

    public function test_self_pickup_requires_planned_pickup_time(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 1, 'BM');

        $response = $this->postJson('/api/bookings', [
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'pickup_type' => 'SELF_PICKUP',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('planned_pickup_at');
    }

    public function test_delivery_requires_address_fields(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 1, 'BM');

        $response = $this->postJson('/api/bookings', [
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'pickup_type' => 'DELIVERY',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'delivery_postal_code',
                'delivery_city',
                'delivery_street',
                'delivery_house_number',
            ]);
    }

    public function test_same_product_cannot_appear_twice(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 2, 'BM');

        $response = $this->postJson('/api/bookings', [
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => now()
                ->addDays(10)
                ->setTime(9, 0)
                ->toDateTimeString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items.0.product_id');
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
    public function test_authenticated_user_with_bearer_token_is_assigned_to_booking(): void
    {
        $user = User::factory()->create([
            'active' => true,
        ]);

        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $this->createInventoryItems($product, 1, 'BM');

        $token = $user->createToken('booking-test-token');

        $response = $this
            ->withToken($token->plainTextToken)
            ->postJson('/api/bookings', [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => '+36301234567',
                'start_date' => now()->addDays(10)->toDateString(),
                'end_date' => now()->addDays(12)->toDateString(),
                'pickup_type' => 'SELF_PICKUP',
                'planned_pickup_at' => now()
                    ->addDays(10)
                    ->setTime(9, 0)
                    ->toDateTimeString(),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('booking.user_id', $user->id);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'status' => 'PENDING',
        ]);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingItemAllocation;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingItemAllocationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_allocation_belongs_to_booking_item(): void
    {
        [$bookingItem, $inventoryItem] = $this->createModels();

        $allocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now(),
            'returned_at' => null,
        ]);

        $this->assertTrue(
            $allocation->bookingItem->is($bookingItem)
        );
    }

    public function test_allocation_belongs_to_inventory_item(): void
    {
        [$bookingItem, $inventoryItem] = $this->createModels();

        $allocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now(),
            'returned_at' => null,
        ]);

        $this->assertTrue(
            $allocation->inventoryItem->is($inventoryItem)
        );
    }

    public function test_booking_item_has_many_allocations(): void
    {
        [$bookingItem, $inventoryItem] = $this->createModels();

        BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now(),
            'returned_at' => null,
        ]);

        $this->assertCount(1, $bookingItem->allocations);
    }

    public function test_inventory_item_has_many_booking_allocations(): void
    {
        [$bookingItem, $inventoryItem] = $this->createModels();

        BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now(),
            'returned_at' => null,
        ]);

        $this->assertCount(
            1,
            $inventoryItem->bookingAllocations
        );
    }

    public function test_allocation_timestamps_are_cast_to_datetime(): void
    {
        [$bookingItem, $inventoryItem] = $this->createModels();

        $allocation = BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => '2026-08-10 09:00:00',
            'returned_at' => '2026-08-12 17:00:00',
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $allocation->assigned_at
        );

        $this->assertInstanceOf(
            Carbon::class,
            $allocation->returned_at
        );
    }

    private function createModels(): array
    {
        $category = Category::query()->create([
            'name' => 'Kisgépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Betonkeverő 180L',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ]);

        $inventoryItem = InventoryItem::query()->create([
            'product_id' => $product->id,
            'inventory_code' => 'BM-001',
            'serial_number' => null,
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ]);

        $booking = Booking::query()->create([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'status' => 'CONFIRMED',
        ]);

        $bookingItem = BookingItem::query()->create([
            'booking_id' => $booking->id,
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 1,
            'price_per_day' => 8000,
            'deposit_per_item' => 30000,
            'rental_days' => 3,
            'rental_subtotal' => 24000,
            'deposit_subtotal' => 30000,
        ]);

        return [$bookingItem, $inventoryItem];
    }
}

<?php

namespace Tests\Unit;

use App\Models\BatteryItem;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingItemAllocation;
use App\Models\BookingItemAllocationBatteryItem;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingItemAllocationBatteryItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_belongs_to_booking_item_allocation(): void
    {
        $allocation = $this->createAllocation();

        $batteryItem = BatteryItem::factory()->create();

        $batteryAllocation =
            BookingItemAllocationBatteryItem::query()->create([
                'booking_item_allocation_id' => $allocation->id,
                'battery_item_id' => $batteryItem->id,
                'assigned_at' => now(),
                'returned_at' => null,
            ]);

        $this->assertTrue(
            $batteryAllocation
                ->bookingItemAllocation
                ->is($allocation)
        );
    }

    public function test_it_belongs_to_battery_item(): void
    {
        $allocation = $this->createAllocation();

        $batteryItem = BatteryItem::factory()->create();

        $batteryAllocation =
            BookingItemAllocationBatteryItem::query()->create([
                'booking_item_allocation_id' => $allocation->id,
                'battery_item_id' => $batteryItem->id,
                'assigned_at' => now(),
                'returned_at' => null,
            ]);

        $this->assertTrue(
            $batteryAllocation
                ->batteryItem
                ->is($batteryItem)
        );
    }

    public function test_assigned_at_and_returned_at_are_cast_to_datetime(): void
    {
        $allocation = $this->createAllocation();

        $batteryItem = BatteryItem::factory()->create();

        $batteryAllocation =
            BookingItemAllocationBatteryItem::query()->create([
                'booking_item_allocation_id' => $allocation->id,
                'battery_item_id' => $batteryItem->id,
                'assigned_at' => now(),
                'returned_at' => now(),
            ]);

        $this->assertInstanceOf(
            Carbon::class,
            $batteryAllocation->assigned_at
        );

        $this->assertInstanceOf(
            Carbon::class,
            $batteryAllocation->returned_at
        );
    }

    private function createAllocation(): BookingItemAllocation
    {
        $product = Product::factory()->create();

        $inventoryItem = InventoryItem::factory()->create([
            'product_id' => $product->id,
        ]);

        $booking = Booking::query()->create([
            'user_id' => null,
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => now()->addHour(),
            'delivery_postal_code' => null,
            'delivery_city' => null,
            'delivery_street' => null,
            'delivery_house_number' => null,
            'delivery_latitude' => null,
            'delivery_longitude' => null,
            'delivery_distance_km' => null,
            'status' => 'CONFIRMED',
            'customer_note' => null,
            'admin_note' => null,
        ]);

        $bookingItem = BookingItem::query()->create([
            'booking_id' => $booking->id,
            'product_id' => $product->id,
            'inventory_item_id' => null,
            'quantity' => 1,
            'price_per_day' => 1000,
            'deposit_per_item' => 5000,
            'rental_days' => 1,
            'rental_subtotal' => 1000,
            'deposit_subtotal' => 5000,
        ]);

        return BookingItemAllocation::query()->create([
            'booking_item_id' => $bookingItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'assigned_at' => now(),
            'returned_at' => null,
        ]);
    }
}
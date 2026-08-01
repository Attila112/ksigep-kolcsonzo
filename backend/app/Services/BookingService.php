<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly BookingAvailabilityService $availabilityService,
        private readonly BookingPriceCalculator $priceCalculator,
    ) {
    }

    /**
     * Creates a booking request with one or more products.
     *
     * Availability and prices are checked on the backend.
     * The booking and its items are saved in one database transaction,
     * so a partially created booking cannot remain in the database.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data, ?User $user = null): Booking
    {
        return DB::transaction(function () use ($data, $user): Booking {
            $preparedItems = $this->prepareItems($data);

            $booking = Booking::query()->create([
                'user_id' => $user?->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => strtolower($data['customer_email']),
                'customer_phone' => $data['customer_phone'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'pickup_type' => $data['pickup_type'],
                'planned_pickup_at' => $data['planned_pickup_at'] ?? null,
                'delivery_postal_code' => $data['delivery_postal_code'] ?? null,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_street' => $data['delivery_street'] ?? null,
                'delivery_house_number' => $data['delivery_house_number'] ?? null,
                'delivery_latitude' => $data['delivery_latitude'] ?? null,
                'delivery_longitude' => $data['delivery_longitude'] ?? null,
                'delivery_distance_km' => $data['delivery_distance_km'] ?? null,
                'status' => 'PENDING',
                'customer_note' => $data['customer_note'] ?? null,
                'admin_note' => null,
            ]);

            foreach ($preparedItems as $preparedItem) {
                $booking->items()->create($preparedItem);
            }

            return $booking->load('items.product');
        });
    }

    /**
     * Checks product availability and calculates the saved booking prices.
     *
     * @param array<string, mixed> $data
     * @return array<int, array<string, int|float|null>>
     */
    private function prepareItems(array $data): array
    {
        $preparedItems = [];

        foreach ($data['items'] as $item) {
            $product = Product::query()
                ->where('active', true)
                ->findOrFail($item['product_id']);

            $requestedQuantity = (int) $item['quantity'];

            $availableQuantity = $this->availabilityService
                ->availableQuantity(
                    product: $product,
                    startDate: $data['start_date'],
                    endDate: $data['end_date'],
                );

            if ($requestedQuantity > $availableQuantity) {
                throw new DomainException(sprintf(
                    'A(z) %s termékből csak %d darab érhető el a kiválasztott időszakban.',
                    $product->name,
                    $availableQuantity,
                ));
            }

            $price = $this->priceCalculator->calculate(
                startDate: $data['start_date'],
                endDate: $data['end_date'],
                pricePerDay: $product->price_per_day,
                depositPerItem: $product->deposit,
                quantity: $requestedQuantity,
            );

            $preparedItems[] = [
                'product_id' => $product->id,
                'inventory_item_id' => null,
                'quantity' => $price['quantity'],
                'price_per_day' => $price['price_per_day'],
                'deposit_per_item' => $price['deposit_per_item'],
                'rental_days' => $price['rental_days'],
                'rental_subtotal' => $price['rental_subtotal'],
                'deposit_subtotal' => $price['deposit_subtotal'],
            ];
        }

        return $preparedItems;
    }
}
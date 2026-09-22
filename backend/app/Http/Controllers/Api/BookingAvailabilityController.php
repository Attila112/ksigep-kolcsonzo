<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingAvailabilityCheckRequest;
use App\Models\Product;
use App\Services\BookingAvailabilityService;
use Illuminate\Http\JsonResponse;

class BookingAvailabilityController extends Controller
{
    public function __construct(
        private readonly BookingAvailabilityService $availabilityService,
    ) {
    }

    public function check(
        BookingAvailabilityCheckRequest $request,
    ): JsonResponse {
        $validated = $request->validated();

        $products = Product::query()
            ->whereIn(
                'id',
                collect($validated['items'])
                    ->pluck('product_id')
            )
            ->get()
            ->keyBy('id');

        $items = collect(
            $validated['items']
        )->map(function (array $item) use (
            $products,
            $validated
        ): array {
            /** @var Product $product */
            $product = $products->get(
                $item['product_id']
            );

            $availableQuantity =
                $this->availabilityService
                    ->availableQuantity(
                        $product,
                        $validated['start_date'],
                        $validated['end_date'],
                    );

            return [
                'product_id' => $product->id,

                'available_quantity' =>
                    $availableQuantity,

                'available' =>
                    $availableQuantity > 0,
            ];
        })->values();

        return response()->json([
            'start_date' =>
                $validated['start_date'],

            'end_date' =>
                $validated['end_date'],

            'items' => $items,
        ]);
    }
}
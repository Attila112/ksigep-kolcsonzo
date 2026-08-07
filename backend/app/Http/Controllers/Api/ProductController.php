<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductAvailabilityRequest;
use App\Models\Product;
use App\Services\BookingAvailabilityService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->where('active', true)
            ->whereHas('category', function ($query) {
                $query->where('active', true);
            })
            ->with('category:id,name')
            ->withCount([
                'reviews as reviews_count' => function ($query) {
                    $query->where('approved', true);
                },
            ])
            ->withAvg([
                'reviews as average_rating' => function ($query) {
                    $query->where('approved', true);
                },
            ], 'rating')
            ->latest()
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        if (
            ! $product->active ||
            ! $product->category()->where('active', true)->exists()
        ) {
            abort(404);
        }

        $product->load([
            'category:id,name',
            'batterySystem:id,name,manufacturer,voltage',
        ]);

        $product->loadCount([
            'reviews as reviews_count' => function ($query) {
                $query->where('approved', true);
            },
        ]);

        $product->loadAvg([
            'reviews as average_rating' => function ($query) {
                $query->where('approved', true);
            },
        ], 'rating');

        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Returns the available quantity of a product
     * for the requested rental period.
     */
    public function availability(
        ProductAvailabilityRequest $request,
        Product $product,
        BookingAvailabilityService $availabilityService,
    ): JsonResponse {
        if (
            ! $product->active ||
            ! $product->category()->where('active', true)->exists()
        ) {
            abort(404);
        }

        $validated = $request->validated();

        $availableQuantity = $availabilityService->availableQuantity(
            product: $product,
            startDate: $validated['start_date'],
            endDate: $validated['end_date'],
        );

        return response()->json([
            'product_id' => $product->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'available_quantity' => $availableQuantity,
            'available' => $availableQuantity > 0,
        ]);
    }
}
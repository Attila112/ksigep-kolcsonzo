<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Admin\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->with([
                'category:id,name',
                'batterySystem:id,name,manufacturer,voltage',
            ])
            ->withCount('inventoryItems')
            ->withCount([
                'inventoryItems as available_inventory_count' => function ($query) {
                    $query->where('status', 'AVAILABLE');
                },
            ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }
    public function show(Product $product): JsonResponse
    {
        $product->load([
            'category:id,name',
            'batterySystem:id,name,manufacturer,voltage',
            'inventoryItems',
            'workTypes:id,name,slug',
        ]);

        $product->loadCount([
            'inventoryItems',
            'inventoryItems as available_inventory_count' => function ($query) {
                $query->where('status', 'AVAILABLE');
            },
            'inventoryItems as rented_inventory_count' => function ($query) {
                $query->where('status', 'RENTED');
            },
            'inventoryItems as maintenance_inventory_count' => function ($query) {
                $query->where('status', 'MAINTENANCE');
            },
        ]);

        return response()->json([
            'product' => $product,
        ]);
    }
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): JsonResponse {
        $validated = $request->validated();

        if (empty($validated['battery_system_id'])) {
            $validated['battery_system_id'] = null;
            $validated['required_batteries'] = 0;
            $validated['required_chargers'] = 0;
        }

        $product->update($validated);

        $product->load([
            'category:id,name',
            'batterySystem:id,name,manufacturer,voltage',
            'inventoryItems',
            'workTypes:id,name,slug',
        ]);

        $product->loadCount([
            'inventoryItems',
            'inventoryItems as available_inventory_count' => function ($query) {
                $query->where('status', 'AVAILABLE');
            },
            'inventoryItems as rented_inventory_count' => function ($query) {
                $query->where('status', 'RENTED');
            },
            'inventoryItems as maintenance_inventory_count' => function ($query) {
                $query->where('status', 'MAINTENANCE');
            },
        ]);

        return response()->json([
            'product' => $product,
        ]);
    }
}

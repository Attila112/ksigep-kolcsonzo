<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

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
}

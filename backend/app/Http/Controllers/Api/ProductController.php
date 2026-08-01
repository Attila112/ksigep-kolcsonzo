<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
}

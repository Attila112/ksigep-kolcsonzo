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
            ->with('category:id,name')
            ->latest()
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }
}
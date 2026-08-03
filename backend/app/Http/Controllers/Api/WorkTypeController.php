<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkType;
use Illuminate\Http\JsonResponse;

class WorkTypeController extends Controller
{
    /**
     * Returns all active work types in display order.
     */
    public function index(): JsonResponse
    {
        $workTypes = WorkType::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'icon_key',
                'sort_order',
            ]);

        return response()->json([
            'work_types' => $workTypes,
        ]);
    }

    /**
     * Returns the active products recommended for an active work type.
     */
    public function products(
        WorkType $workType
    ): JsonResponse {
        if (! $workType->active) {
            abort(404);
        }

        $products = $workType
            ->products()
            ->where('products.active', true)
            ->whereHas(
                'category',
                fn($query) => $query->where('active', true)
            )
            ->with('category:id,name')
            ->orderBy('products.name')
            ->get();

        return response()->json([
            'work_type' => [
                'id' => $workType->id,
                'name' => $workType->name,
                'slug' => $workType->slug,
                'description' => $workType->description,
                'icon_key' => $workType->icon_key,
            ],
            'products' => $products,
        ]);
    }
}

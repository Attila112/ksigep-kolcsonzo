<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreInventoryItemRequest;

class InventoryItemController extends Controller
{
    public function index(): JsonResponse
    {
        $inventoryItems = InventoryItem::query()
            ->with('product:id,name')
            ->latest()
            ->get();

        return response()->json([
            'inventory_items' => $inventoryItems,
        ]);
    }
    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $inventoryItem = InventoryItem::query()->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'A géppéldány sikeresen létrejött.',
            'inventory_item' => $inventoryItem,
        ], 201);
    }
}

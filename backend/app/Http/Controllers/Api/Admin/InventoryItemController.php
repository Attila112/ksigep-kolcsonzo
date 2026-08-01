<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;

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
}
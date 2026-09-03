<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatteryItem;
use Illuminate\Http\JsonResponse;

class BatteryItemController extends Controller
{
    public function index(): JsonResponse
    {
        $batteryItems = BatteryItem::query()
            ->with([
                'batterySystem:id,name,manufacturer,voltage',
            ])
            ->latest()
            ->get();

        return response()->json([
            'battery_items' => $batteryItems,
        ]);
    }

    public function show(
        BatteryItem $batteryItem
    ): JsonResponse {
        $batteryItem->load([
            'batterySystem:id,name,manufacturer,voltage',
        ]);

        return response()->json([
            'battery_item' => $batteryItem,
        ]);
    }
}
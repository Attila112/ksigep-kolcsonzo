<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatteryItem;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Admin\UpdateBatteryStatusRequest;
use App\Services\BatteryStatusService;
use DomainException;

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
    public function updateStatus(
        UpdateBatteryStatusRequest $request,
        BatteryItem $batteryItem,
        BatteryStatusService $batteryStatusService
    ): JsonResponse {
        try {
            $batteryItem = $batteryStatusService->update(
                batteryItem: $batteryItem,
                status: $request->validated('status'),
                adminNote: $request->validated('admin_note') ?? null,
                changedBy: $request->user(),
            );
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'battery_item' => $batteryItem,
        ]);
    }
    public function statusHistory(
        BatteryItem $batteryItem
    ): JsonResponse {
        $statusHistory = $batteryItem
            ->statusHistories()
            ->with('changedBy:id,name')
            ->latest()
            ->get();

        return response()->json([
            'battery_item' => [
                'id' => $batteryItem->id,
                'inventory_code' => $batteryItem->inventory_code,
                'status' => $batteryItem->status,
            ],
            'status_history' => $statusHistory,
        ]);
    }
}

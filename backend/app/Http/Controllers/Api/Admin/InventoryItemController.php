<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryStatusRequest;
use App\Services\InventoryStatusService;
use DomainException;

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

    /**
     * Updates the operational status of a physical machine.
     */
    public function updateStatus(
        UpdateInventoryStatusRequest $request,
        InventoryItem $inventoryItem,
        InventoryStatusService $statusService,
    ): JsonResponse {
        try {
            $inventoryItem = $statusService->update(
                inventoryItem: $inventoryItem,
                status: $request->validated('status'),
                adminNote: $request->validated('admin_note'),
            );
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'A gép állapota sikeresen módosítva.',
            'inventory_item' => $inventoryItem,
        ]);
    }
}

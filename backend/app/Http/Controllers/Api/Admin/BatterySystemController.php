<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatterySystem;
use Illuminate\Http\JsonResponse;

class BatterySystemController extends Controller
{
    public function index(): JsonResponse
    {
        $batterySystems = BatterySystem::query()
            ->orderBy('manufacturer')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'manufacturer',
                'voltage',
                'active',
            ]);

        return response()->json([
            'battery_systems' => $batterySystems,
        ]);
    }
}
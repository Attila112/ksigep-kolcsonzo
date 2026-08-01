<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    /**
     * Returns every booking for the admin,
     * including booked products and calculated totals.
     */
    public function index(): JsonResponse
    {
        $bookings = Booking::query()
            ->with('items.product:id,name')
            ->latest()
            ->get();

        return response()->json([
            'bookings' => $bookings,
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    /**
     * Creates a new booking request for a guest or authenticated user.
     *
     * Availability and prices are always recalculated on the backend.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->create(
                data: $request->validated(),
                user: $request->user('sanctum'),
            );
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'A foglalási kérelmet sikeresen elküldted.',
            'booking' => $booking,
        ], 201);
    }
    /**
     * Returns the authenticated user's bookings,
     * including the booked products and calculated totals.
     */
    public function indexMine(Request $request): JsonResponse
    {
        $bookings = $request->user()
            ->bookings()
            ->with('items.product:id,name')
            ->latest()
            ->get();

        return response()->json([
            'bookings' => $bookings,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use DomainException;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {
    }

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
}
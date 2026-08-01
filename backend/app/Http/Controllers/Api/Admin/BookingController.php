<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use App\Services\BookingApprovalService;
use DomainException;
use App\Http\Requests\RejectBookingRequest;
use App\Services\BookingRejectionService;

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
    /**
     * Approves a pending booking after a final availability check.
     */
    public function approve(
        Booking $booking,
        BookingApprovalService $approvalService,
    ): JsonResponse {
        try {
            $booking = $approvalService->approve($booking);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'A foglalás sikeresen jóváhagyva.',
            'booking' => $booking,
        ]);
    }
    /**
     * Rejects a pending booking and stores the admin reason.
     */
    public function reject(
        RejectBookingRequest $request,
        Booking $booking,
        BookingRejectionService $rejectionService,
    ): JsonResponse {
        try {
            $booking = $rejectionService->reject(
                booking: $booking,
                reason: $request->validated('reason'),
            );
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'A foglalás sikeresen elutasítva.',
            'booking' => $booking,
        ]);
    }
}

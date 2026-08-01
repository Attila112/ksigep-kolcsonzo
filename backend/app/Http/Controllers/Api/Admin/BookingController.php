<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use App\Services\BookingApprovalService;
use DomainException;
use App\Http\Requests\RejectBookingRequest;
use App\Services\BookingRejectionService;
use App\Http\Requests\IssueBookingRequest;
use App\Services\BookingIssueService;
use App\Http\Requests\ReturnBookingItemsRequest;
use App\Services\BookingReturnService;

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
    /**
     * Assigns physical machines and activates a confirmed booking.
     */
    public function issue(
        IssueBookingRequest $request,
        Booking $booking,
        BookingIssueService $issueService,
    ): JsonResponse {
        try {
            $booking = $issueService->issue(
                booking: $booking,
                inventoryItemIds: $request->validated('inventory_item_ids'),
            );
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'A foglalás gépei sikeresen kiadásra kerültek.',
            'booking' => $booking,
        ]);
    }
    /**
     * Returns selected physical machines from an active booking.
     *
     * Returned machines enter INSPECTION status. The booking becomes
     * COMPLETED only when every allocated machine has been returned.
     */
    public function returnItems(
        ReturnBookingItemsRequest $request,
        Booking $booking,
        BookingReturnService $returnService,
    ): JsonResponse {
        try {
            $booking = $returnService->returnItems(
                booking: $booking,
                inventoryItemIds: $request->validated(
                    'inventory_item_ids'
                ),
            );
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' =>
            'A kiválasztott gépek sikeresen visszavételre kerültek.',
            'booking' => $booking,
        ]);
    }
}

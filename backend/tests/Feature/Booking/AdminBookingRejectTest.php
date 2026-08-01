<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBookingRejectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reject_pending_booking_with_reason(): void
    {
        $admin = $this->createAdmin();

        $booking = $this->createBooking([
            'status' => 'PENDING',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/reject",
            [
                'reason' => 'A kiválasztott időszakban nem tudjuk biztosítani a gépet.',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'message',
                'A foglalás sikeresen elutasítva.'
            )
            ->assertJsonPath('booking.id', $booking->id)
            ->assertJsonPath('booking.status', 'REJECTED')
            ->assertJsonPath(
                'booking.admin_note',
                'A kiválasztott időszakban nem tudjuk biztosítani a gépet.'
            );

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'REJECTED',
            'admin_note' =>
            'A kiválasztott időszakban nem tudjuk biztosítani a gépet.',
        ]);
    }

    public function test_rejection_reason_is_required(): void
    {
        $admin = $this->createAdmin();
        $booking = $this->createBooking();

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/reject",
            []
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reason');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_only_pending_booking_can_be_rejected(): void
    {
        $admin = $this->createAdmin();

        $booking = $this->createBooking([
            'status' => 'CONFIRMED',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/admin/bookings/{$booking->id}/reject",
            [
                'reason' => 'Teszt indok.',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Csak függőben lévő foglalás utasítható el.'
            );

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'CONFIRMED',
        ]);
    }

    public function test_customer_cannot_reject_booking(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
            'active' => true,
        ]);

        $booking = $this->createBooking();

        Sanctum::actingAs($customer);

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/reject",
            [
                'reason' => 'Teszt indok.',
            ]
        )->assertForbidden();
    }

    public function test_guest_cannot_reject_booking(): void
    {
        $booking = $this->createBooking();

        $this->postJson(
            "/api/admin/bookings/{$booking->id}/reject",
            [
                'reason' => 'Teszt indok.',
            ]
        )->assertUnauthorized();
    }

    public function test_missing_booking_returns_not_found(): void
    {
        $admin = $this->createAdmin();

        Sanctum::actingAs($admin);

        $this->postJson(
            '/api/admin/bookings/999999/reject',
            [
                'reason' => 'Teszt indok.',
            ]
        )->assertNotFound();
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'ADMIN',
            'active' => true,
        ]);
    }

    private function createBooking(array $attributes = []): Booking
    {
        return Booking::query()->create(array_merge([
            'customer_name' => 'Teszt Elek',
            'customer_email' => 'teszt@example.com',
            'customer_phone' => '+36301234567',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'pickup_type' => 'SELF_PICKUP',
            'planned_pickup_at' => '2026-08-10 09:00:00',
            'status' => 'PENDING',
            'admin_note' => null,
        ], $attributes));
    }
}

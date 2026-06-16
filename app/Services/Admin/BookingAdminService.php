<?php

namespace App\Services\Admin;

use App\Mail\TicketsBookedMail;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingAdminService
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function updateStatuses(Booking $booking, array $payload): Booking
    {
        return DB::transaction(function () use ($booking, $payload) {
            $booking->update([
                'status' => $payload['status'],
                'payment_status' => $payload['payment_status'],
            ]);

            $payment = $booking->payment()->latest()->first();

            if ($payment) {
                $payment->update([
                    'status' => $payload['payment_status'],
                    'paid_at' => $payload['payment_status'] === 'paid' ? ($payment->paid_at ?? now()) : $payment->paid_at,
                    'payload' => array_merge($payment->payload ?? [], [
                        'admin_status_sync_at' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            $this->activityLogService->log(
                auth()->id(),
                'booking.status_updated',
                $booking,
                "تم تحديث حالة الحجز {$booking->reference}",
                [
                    'status' => $payload['status'],
                    'payment_status' => $payload['payment_status'],
                ]
            );

            return $booking->fresh(['user', 'event', 'items.ticket', 'latestPayment']);
        });
    }

    public function resendTickets(Booking $booking): void
    {
        if (! filled(config('mail.from.address'))) {
            return;
        }

        $booking->loadMissing(['event', 'items.ticket', 'latestPayment']);

        Mail::to($booking->customer_email)->queue(new TicketsBookedMail($booking));

        $this->activityLogService->log(
            auth()->id(),
            'booking.ticket_resent',
            $booking,
            "تمت إعادة إرسال تذاكر الحجز {$booking->reference}",
            ['customer_email' => $booking->customer_email]
        );
    }
}

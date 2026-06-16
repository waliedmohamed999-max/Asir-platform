<?php

namespace App\Services\Admin;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentAdminService
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function updateStatus(Payment $payment, string $status): Payment
    {
        return DB::transaction(function () use ($payment, $status) {
            $payment->update([
                'status' => $status,
                'paid_at' => $status === 'paid' ? ($payment->paid_at ?? now()) : $payment->paid_at,
                'payload' => array_merge($payment->payload ?? [], [
                    'admin_reviewed_at' => now()->toDateTimeString(),
                ]),
            ]);

            $booking = $payment->booking;

            if ($booking) {
                $booking->update([
                    'payment_status' => $status,
                    'status' => match ($status) {
                        'paid' => 'paid',
                        'refunded' => 'refunded',
                        'failed' => 'failed',
                        default => $booking->status,
                    },
                ]);
            }

            $this->activityLogService->log(
                auth()->id(),
                'payment.status_updated',
                $payment,
                "تم تحديث حالة العملية {$payment->transaction_reference}",
                [
                    'status' => $status,
                    'booking_reference' => $booking?->reference,
                ]
            );

            return $payment->fresh(['booking.user', 'booking.event']);
        });
    }
}

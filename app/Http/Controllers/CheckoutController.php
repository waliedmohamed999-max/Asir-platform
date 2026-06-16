<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CheckoutController extends Controller
{
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('checkout.show', compact('booking'));
    }

    public function pay(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        $request->validate([
            'payment_method' => 'required|in:stripe,paypal,mada',
        ]);

        $booking->status = 'paid';
        $booking->save();

        $booking->payment()->create([
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'transaction_id' => strtoupper(uniqid('TRX-')),
            'amount' => $booking->total_amount - $booking->discount_amount,
            'paid_at' => now(),
        ]);

        return redirect()->route('bookings.index')->with('success', 'تم تأكيد الدفع بنجاح.');
    }
}

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تذكرة الحجز {{ $booking->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .page { padding: 20px; }
        .hero { background: #0f172a; color: #fff; border-radius: 18px; padding: 20px; }
        .hero h1 { margin: 0; font-size: 24px; }
        .hero p { margin: 6px 0 0; color: #cbd5e1; }
        .grid { width: 100%; margin-top: 18px; }
        .grid td { vertical-align: top; width: 50%; padding: 0 0 12px 12px; }
        .card { border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px; }
        .label { color: #64748b; font-size: 10px; margin-bottom: 4px; }
        .value { font-size: 14px; font-weight: 700; }
        .ticket { border: 1px solid #cbd5e1; border-radius: 18px; margin-top: 16px; overflow: hidden; }
        .ticket-head { background: #f8fafc; padding: 14px 16px; }
        .ticket-body { padding: 16px; }
        .muted { color: #64748b; font-size: 11px; }
        .barcode { margin-top: 10px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; }
    </style>
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>تذكرة منصة عسير</h1>
            <p>مرجع الحجز: {{ $booking->reference }}</p>
            <p>{{ $booking->event->title }} • {{ $booking->event->city?->name }} • {{ $booking->event->venue_name }}</p>
        </div>

        <table class="grid" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <div class="card">
                        <div class="label">اسم العميل</div>
                        <div class="value">{{ $booking->user?->name ?? $booking->customer_email }}</div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="label">تاريخ الحجز</div>
                        <div class="value">{{ $booking->booking_date?->translatedFormat('d M Y h:i A') }}</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="card">
                        <div class="label">إجمالي الدفع</div>
                        <div class="value">{{ number_format($booking->total_amount, 2) }} ر.س</div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="label">طريقة الدفع</div>
                        <div class="value">{{ strtoupper($booking->latestPayment?->gateway ?? 'N/A') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        @foreach($booking->items as $item)
            <div class="ticket">
                <div class="ticket-head">
                    <div class="value">{{ $item->ticket_name }}</div>
                    <div class="muted">الكمية: {{ $item->quantity }} • التاريخ: {{ $item->attendee_date?->translatedFormat('d M Y') }}</div>
                </div>
                <div class="ticket-body">
                    <div class="label">كود التذكرة</div>
                    <div class="value">{{ strtoupper($item->qr_token) }}</div>
                    <div class="barcode">
                        {!! \App\Support\Code39Barcode::svg($item->qr_token, 70) !!}
                    </div>
                    <p class="muted">يمكن استخدام هذا الباركود عند الدخول أو التحقق اليدوي من التذكرة.</p>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>

<div style="font-family:Tahoma,Segoe UI,sans-serif;direction:rtl">
    <h2>تم تأكيد حجزك في منصة عسير</h2>
    <p>مرجع الحجز: {{ $booking->reference }}</p>
    <p>الفعالية: {{ $booking->event->title }}</p>
    <p>الإجمالي: {{ number_format($booking->total_amount, 2) }} ر.س</p>
    <p>يمكنك عرض التذكرة وباركود الدخول من خلال حسابك داخل المنصة.</p>
    @if($booking->items->isNotEmpty())
        <div style="margin-top:16px;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc">
            <p style="margin:0 0 8px;font-weight:700">أكواد التذاكر</p>
            @foreach($booking->items as $item)
                <p style="margin:6px 0">{{ $item->ticket_name }}: <span style="font-family:monospace">{{ strtoupper($item->qr_token) }}</span></p>
            @endforeach
        </div>
    @endif
</div>

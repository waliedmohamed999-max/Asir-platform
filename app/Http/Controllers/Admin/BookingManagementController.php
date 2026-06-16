<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminBookingUpdateRequest;
use App\Models\Booking;
use App\Services\Admin\BookingAdminService;
use App\Services\Admin\ExportService;
use Illuminate\Http\Request;

class BookingManagementController extends Controller
{
    public function __construct(
        private readonly BookingAdminService $bookingAdminService,
        private readonly ExportService $exportService
    )
    {
    }

    public function index(Request $request)
    {
        $bookings = $this->filteredQuery($request)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statusCounts = [
            'all' => Booking::count(),
            'paid' => Booking::whereIn('status', ['paid', 'confirmed'])->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'refunded' => Booking::where('status', 'refunded')->count(),
        ];

        $gateways = ['stripe', 'paypal', 'mada', 'cash'];
        $bookingStatuses = ['pending', 'confirmed', 'paid', 'failed', 'cancelled', 'refunded', 'completed'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

        return view('admin.bookings.index', compact('bookings', 'statusCounts', 'gateways', 'bookingStatuses', 'paymentStatuses'));
    }

    public function exportCsv(Request $request)
    {
        [$headers, $rows] = $this->exportDataset($request);

        return $this->exportService->csv('admin-bookings.csv', $headers, $rows);
    }

    public function exportXlsx(Request $request)
    {
        [$headers, $rows] = $this->exportDataset($request);

        return $this->exportService->xlsx('admin-bookings.xlsx', $headers, $rows);
    }

    public function exportPdf(Request $request)
    {
        $bookings = $this->filteredQuery($request)->latest()->get();

        return $this->exportService->pdf(
            'admin-bookings.pdf',
            'تقرير الحجوزات',
            'تصدير PDF رسمي للحجوزات من لوحة الإدارة.',
            [
                'عدد السجلات' => $bookings->count(),
                'إجمالي القيمة' => number_format((float) $bookings->sum('total_amount'), 2).' ر.س',
            ],
            ['المرجع', 'العميل', 'الفعالية', 'التذاكر', 'الإجمالي', 'الحالة', 'حالة الدفع', 'التاريخ'],
            $bookings->map(fn ($booking) => [
                $booking->reference,
                $booking->user?->name ?? '—',
                $booking->event?->title ?? '—',
                $booking->items->sum('quantity'),
                number_format($booking->total_amount, 2).' ر.س',
                $booking->status,
                $booking->payment_status,
                optional($booking->booking_date)->translatedFormat('d M Y - h:i A'),
            ])->all()
        );
    }

    public function print(Request $request)
    {
        $bookings = $this->filteredQuery($request)->latest()->get();

        $rows = $bookings->map(fn ($booking) => [
            $booking->reference,
            $booking->user?->name ?? '—',
            $booking->event?->title ?? '—',
            $booking->items->sum('quantity'),
            number_format($booking->total_amount, 2).' ر.س',
            $booking->status,
            $booking->payment_status,
            optional($booking->booking_date)->translatedFormat('d M Y - h:i A'),
        ])->all();

        return view('admin.exports.printable', [
            'title' => 'تقرير الحجوزات',
            'subtitle' => 'نسخة جاهزة للطباعة والحفظ بصيغة PDF من المتصفح.',
            'summary' => [
                'عدد السجلات' => $bookings->count(),
                'إجمالي القيمة' => number_format((float) $bookings->sum('total_amount'), 2).' ر.س',
            ],
            'headers' => ['المرجع', 'العميل', 'الفعالية', 'التذاكر', 'الإجمالي', 'الحالة', 'حالة الدفع', 'التاريخ'],
            'rows' => $rows,
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'event.city', 'event.category', 'coupon', 'items.ticket', 'payment']);

        $bookingStatuses = ['pending', 'confirmed', 'paid', 'failed', 'cancelled', 'refunded', 'completed'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

        return view('admin.bookings.show', compact('booking', 'bookingStatuses', 'paymentStatuses'));
    }

    public function update(AdminBookingUpdateRequest $request, Booking $booking)
    {
        $this->bookingAdminService->updateStatuses($booking, $request->validated());

        return back()->with('success', 'تم تحديث حالة الحجز والدفع بنجاح.');
    }

    public function resend(Booking $booking)
    {
        $this->bookingAdminService->resendTickets($booking);

        return back()->with('success', 'تمت إعادة إرسال التذكرة إلى بريد العميل.');
    }

    private function filteredQuery(Request $request)
    {
        return Booking::query()
            ->with(['user', 'event', 'latestPayment', 'items'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($builder) use ($search) {
                    $builder->where('reference', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('event', fn ($eventQuery) => $eventQuery->where('title', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')->toString()))
            ->when($request->filled('gateway'), fn ($query) => $query->whereHas('latestPayment', fn ($paymentQuery) => $paymentQuery->where('gateway', $request->string('gateway')->toString())))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('booking_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('booking_date', '<=', $request->input('date_to')));
    }

    private function exportDataset(Request $request): array
    {
        $headers = [
            'Reference', 'Customer', 'Email', 'Phone', 'Event', 'Tickets', 'Status', 'Payment Status', 'Gateway', 'Total', 'Booking Date',
        ];

        $rows = $this->filteredQuery($request)->latest()->get()->map(function ($booking) {
            return [
                $booking->reference,
                $booking->user?->name,
                $booking->customer_email,
                $booking->customer_phone,
                $booking->event?->title,
                $booking->items->sum('quantity'),
                $booking->status,
                $booking->payment_status,
                strtoupper($booking->latestPayment?->gateway ?? 'N/A'),
                number_format((float) $booking->total_amount, 2, '.', ''),
                optional($booking->booking_date)->format('Y-m-d H:i'),
            ];
        })->all();

        return [$headers, $rows];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Payment;
use App\Services\Admin\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function __construct(private readonly ExportService $exportService)
    {
    }

    public function index(Request $request)
    {
        $bookingsQuery = $this->filteredQuery($request);

        $bookings = (clone $bookingsQuery)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $paidBase = (clone $bookingsQuery);
        $cancelledBookingsQuery = Booking::query()->where('status', 'cancelled');
        $this->applyDateFilters($cancelledBookingsQuery, $request);

        $topEventsQuery = Booking::query()
            ->select('event_id', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as bookings_count'))
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->where('payment_status', 'paid');
        $this->applyDateFilters($topEventsQuery, $request);

        $citySalesQuery = Booking::query()
            ->join('events', 'events.id', '=', 'bookings.event_id')
            ->join('cities', 'cities.id', '=', 'events.city_id')
            ->whereIn('bookings.status', ['paid', 'confirmed', 'completed'])
            ->where('bookings.payment_status', 'paid');
        $this->applyDateFilters($citySalesQuery, $request, 'bookings.booking_date');

        $lowPerformingQuery = Booking::query()
            ->select('event_id', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as bookings_count'))
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->where('payment_status', 'paid');
        $this->applyDateFilters($lowPerformingQuery, $request);

        $couponUsageQuery = Booking::query()
            ->select('coupon_id', DB::raw('COUNT(*) as uses_count'), DB::raw('SUM(discount_amount) as total_discount'))
            ->whereNotNull('coupon_id')
            ->where('payment_status', 'paid');
        $this->applyDateFilters($couponUsageQuery, $request);

        return view('admin.reports.sales', [
            'bookings' => $bookings,
            'totalSales' => (clone $paidBase)->sum('total_amount'),
            'averageOrder' => (clone $paidBase)->avg('total_amount'),
            'dailySales' => (clone $paidBase)->whereDate('booking_date', today())->sum('total_amount'),
            'weeklySales' => (clone $paidBase)->whereDate('booking_date', '>=', now()->startOfWeek())->sum('total_amount'),
            'monthlySales' => (clone $paidBase)->whereMonth('booking_date', now()->month)->whereYear('booking_date', now()->year)->sum('total_amount'),
            'failedPaymentsCount' => Payment::query()
                ->where('status', 'failed')
                ->whereHas('booking', fn ($query) => $this->applyDateFilters($query, $request))
                ->count(),
            'cancelledBookingsCount' => $cancelledBookingsQuery->count(),
            'topEvents' => $topEventsQuery
                ->groupBy('event_id')
                ->with('event')
                ->orderByDesc('revenue')
                ->take(5)
                ->get(),
            'citySales' => $citySalesQuery
                ->groupBy('cities.name')
                ->select('cities.name as city_name', DB::raw('SUM(bookings.total_amount) as revenue'))
                ->orderByDesc('revenue')
                ->take(6)
                ->get(),
            'gatewaySales' => Payment::query()
                ->select('gateway', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as operations'))
                ->where('status', 'paid')
                ->whereHas('booking', fn ($query) => $this->applyDateFilters($query, $request))
                ->groupBy('gateway')
                ->orderByDesc('total')
                ->get(),
            'lowPerformingEvents' => $lowPerformingQuery
                ->groupBy('event_id')
                ->with('event')
                ->orderBy('revenue')
                ->take(5)
                ->get(),
            'couponUsage' => $couponUsageQuery
                ->groupBy('coupon_id')
                ->with('coupon')
                ->orderByDesc('uses_count')
                ->take(6)
                ->get(),
            'activeCouponsCount' => Coupon::query()->active()->count(),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $bookings = $this->filteredQuery($request)->latest()->get();

        [$headers, $rows] = $this->exportDataset($bookings);

        return $this->exportService->csv('sales-report.csv', $headers, $rows);
    }

    public function exportXlsx(Request $request)
    {
        $bookings = $this->filteredQuery($request)->latest()->get();
        [$headers, $rows] = $this->exportDataset($bookings);

        return $this->exportService->xlsx('sales-report.xlsx', $headers, $rows);
    }

    public function exportPdf(Request $request)
    {
        $bookings = $this->filteredQuery($request)->latest()->get();

        return $this->exportService->pdf(
            'sales-report.pdf',
            'تقرير المبيعات',
            'تصدير PDF رسمي لتقرير المبيعات.',
            [
                'عدد الحجوزات' => $bookings->count(),
                'إجمالي المبيعات' => number_format((float) $bookings->sum('total_amount'), 2).' ر.س',
            ],
            ['المرجع', 'العميل', 'الفعالية', 'المدينة', 'الإجمالي', 'البوابة', 'التاريخ'],
            $bookings->map(fn ($booking) => [
                $booking->reference,
                $booking->user?->name ?? '—',
                $booking->event?->title ?? '—',
                $booking->event?->city?->name ?? '—',
                number_format($booking->total_amount, 2).' ر.س',
                strtoupper($booking->latestPayment?->gateway ?? 'N/A'),
                optional($booking->booking_date)->translatedFormat('d M Y - h:i A'),
            ])->all()
        );
    }

    public function print(Request $request)
    {
        $bookings = $this->filteredQuery($request)->latest()->get();

        return view('admin.exports.printable', [
            'title' => 'تقرير المبيعات',
            'subtitle' => 'نسخة جاهزة للطباعة والحفظ بصيغة PDF من المتصفح.',
            'summary' => [
                'عدد الحجوزات' => $bookings->count(),
                'إجمالي المبيعات' => number_format((float) $bookings->sum('total_amount'), 2).' ر.س',
            ],
            'headers' => ['المرجع', 'العميل', 'الفعالية', 'المدينة', 'الإجمالي', 'البوابة', 'التاريخ'],
            'rows' => $bookings->map(fn ($booking) => [
                $booking->reference,
                $booking->user?->name ?? '—',
                $booking->event?->title ?? '—',
                $booking->event?->city?->name ?? '—',
                number_format($booking->total_amount, 2).' ر.س',
                strtoupper($booking->latestPayment?->gateway ?? 'N/A'),
                optional($booking->booking_date)->translatedFormat('d M Y - h:i A'),
            ])->all(),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = Booking::with(['event.city', 'user', 'latestPayment'])
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->where('payment_status', 'paid');

        return $this->applyDateFilters($query, $request);
    }

    private function applyDateFilters($query, Request $request, string $column = 'booking_date')
    {
        return $query
            ->when($request->filled('date_from'), fn ($builder) => $builder->whereDate($column, '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($builder) => $builder->whereDate($column, '<=', $request->input('date_to')));
    }

    private function exportDataset($bookings): array
    {
        $headers = ['Booking Reference', 'Customer', 'Event', 'City', 'Total', 'Gateway', 'Booking Date'];

        $rows = $bookings->map(fn ($booking) => [
            $booking->reference,
            $booking->user?->name ?? '—',
            $booking->event?->title ?? '—',
            $booking->event?->city?->name ?? '—',
            number_format((float) $booking->total_amount, 2, '.', ''),
            strtoupper($booking->latestPayment?->gateway ?? 'N/A'),
            optional($booking->booking_date)->format('Y-m-d H:i'),
        ])->all();

        return [$headers, $rows];
    }
}

<?php

namespace App\Services\Admin;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\HomepageItem;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminDashboardService
{
    public function build(): array
    {
        $now = now();
        $paidBookings = Booking::query()->paid();
        $allBookings = Booking::query();
        $allEvents = Event::query();
        $payments = Payment::query();

        $salesValue = (float) $paidBookings->sum('total_amount');
        $bookingsCount = (int) $allBookings->count();
        $ticketsSold = (int) BookingItem::sum('quantity');
        $conversionRate = $bookingsCount > 0
            ? round(($allBookings->where('payment_status', 'paid')->count() / $bookingsCount) * 100, 1)
            : 0;
        $averageOrderValue = $bookingsCount > 0 ? round($salesValue / max($allBookings->where('payment_status', 'paid')->count(), 1), 2) : 0;
        $todayBookings = (int) Booking::whereDate('booking_date', $now->toDateString())->count();
        $weekBookings = (int) Booking::whereBetween('booking_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $monthBookings = (int) Booking::whereBetween('booking_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count();
        $todayRevenue = (float) Booking::paid()->whereDate('booking_date', $now->toDateString())->sum('total_amount');
        $monthRevenue = (float) Booking::paid()->whereBetween('booking_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum('total_amount');
        $activeAds = (int) HomepageItem::active()->count();
        $activeCoupons = (int) Coupon::active()->count();
        $publishedEvents = (int) Event::where('status', 'published')->count();
        $draftEvents = (int) Event::where('status', 'draft')->count();
        $featuredEvents = (int) Event::where('is_featured', true)->count();
        $soldOutEvents = (int) Event::where('status', 'sold_out')->count();
        $failedPayments = (int) $payments->where('status', 'failed')->count();
        $pendingPayments = (int) $payments->whereIn('status', ['pending', 'processing'])->count();
        $upcomingEvents = (int) Event::where('start_date', '>=', $now)->count();
        $endedEvents = (int) Event::where('status', 'ended')->orWhere('end_date', '<', $now)->count();

        return [
            'stats' => [
                'events' => (int) $allEvents->count(),
                'tickets_sold' => $ticketsSold,
                'bookings' => $bookingsCount,
                'bookings_today' => $todayBookings,
                'bookings_this_week' => $weekBookings,
                'bookings_this_month' => $monthBookings,
                'users' => User::count(),
                'organizers' => User::where('role', 'organizer')->count(),
                'active_ads' => $activeAds,
                'active_coupons' => $activeCoupons,
                'sales' => $salesValue,
                'today_revenue' => $todayRevenue,
                'month_revenue' => $monthRevenue,
                'conversion_rate' => $conversionRate,
                'average_order_value' => $averageOrderValue,
                'published_events' => $publishedEvents,
                'draft_events' => $draftEvents,
                'featured_events' => $featuredEvents,
                'sold_out_events' => $soldOutEvents,
                'upcoming_events' => $upcomingEvents,
                'ended_events' => $endedEvents,
                'failed_payments' => $failedPayments,
                'pending_payments' => $pendingPayments,
            ],
            'recentBookings' => Booking::with(['user', 'event', 'latestPayment'])->latest()->take(8)->get(),
            'latestEvents' => Event::with(['city', 'category'])->latest()->take(6)->get(),
            'salesChart' => $this->salesChart(),
            'cityStats' => $this->bookingsByCity(),
            'categoryStats' => $this->bookingsByCategory(),
            'eventTypeStats' => $this->ticketsByType(),
            'statusOverview' => $this->statusOverview($publishedEvents, $draftEvents, $featuredEvents, $soldOutEvents, $activeAds, $activeCoupons),
            'paymentGatewayStats' => $this->paymentsByGateway(),
            'topEvents' => $this->topEvents(),
        ];
    }

    private function salesChart(): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))->map(function (int $offset) use ($start) {
            $date = $start->copy()->addMonths($offset);

            return [
                'key' => $date->format('Y-m'),
                'label' => $date->locale('ar')->translatedFormat('M'),
                'total' => 0,
            ];
        })->keyBy('key');

        Booking::query()
            ->paid()
            ->where('booking_date', '>=', $start)
            ->get()
            ->groupBy(fn (Booking $booking) => Carbon::parse($booking->booking_date)->format('Y-m'))
            ->each(function (Collection $items, string $monthKey) use ($months) {
                if ($months->has($monthKey)) {
                    $entry = $months->get($monthKey);
                    $entry['total'] = round((float) $items->sum('total_amount'), 2);
                    $months->put($monthKey, $entry);
                }
            });

        return $months->values()->all();
    }

    private function bookingsByCity(): Collection
    {
        return Booking::query()
            ->with('event.city')
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->event?->city?->name ?? 'غير محدد')
            ->map(fn (Collection $items, string $city) => [
                'label' => $city,
                'count' => $items->count(),
                'revenue' => round((float) $items->where('payment_status', 'paid')->sum('total_amount'), 2),
            ])
            ->sortByDesc('count')
            ->take(6)
            ->values();
    }

    private function bookingsByCategory(): Collection
    {
        return Booking::query()
            ->with('event.category')
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->event?->category?->name ?? 'غير محدد')
            ->map(fn (Collection $items, string $category) => [
                'label' => $category,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->take(6)
            ->values();
    }

    private function ticketsByType(): Collection
    {
        return BookingItem::query()
            ->with('ticket')
            ->get()
            ->groupBy(fn (BookingItem $item) => $item->ticket?->type ?? 'غير محدد')
            ->map(fn (Collection $items, string $type) => [
                'label' => $type,
                'count' => (int) $items->sum('quantity'),
            ])
            ->sortByDesc('count')
            ->take(6)
            ->values();
    }

    private function statusOverview(
        int $publishedEvents,
        int $draftEvents,
        int $featuredEvents,
        int $soldOutEvents,
        int $activeAds,
        int $activeCoupons
    ): array {
        return [
            [
                'label' => 'فعاليات منشورة',
                'value' => $publishedEvents,
                'tone' => 'success',
            ],
            [
                'label' => 'مسودات بانتظار النشر',
                'value' => $draftEvents,
                'tone' => 'warning',
            ],
            [
                'label' => 'فعاليات مميزة',
                'value' => $featuredEvents,
                'tone' => 'info',
            ],
            [
                'label' => 'فعاليات نفدت',
                'value' => $soldOutEvents,
                'tone' => 'danger',
            ],
            [
                'label' => 'إعلانات نشطة',
                'value' => $activeAds,
                'tone' => 'info',
            ],
            [
                'label' => 'كوبونات فعالة',
                'value' => $activeCoupons,
                'tone' => 'success',
            ],
        ];
    }

    private function paymentsByGateway(): Collection
    {
        return Payment::query()
            ->get()
            ->groupBy(fn (Payment $payment) => $payment->gateway ?: 'غير محدد')
            ->map(fn (Collection $items, string $gateway) => [
                'label' => $gateway,
                'count' => $items->count(),
                'paid_total' => round((float) $items->where('status', 'paid')->sum('amount'), 2),
            ])
            ->sortByDesc('paid_total')
            ->take(5)
            ->values();
    }

    private function topEvents(): Collection
    {
        return Event::query()
            ->with(['city', 'category', 'bookings'])
            ->get()
            ->map(function (Event $event) {
                $paidBookings = $event->bookings->where('payment_status', 'paid');

                return [
                    'title' => $event->title,
                    'city' => $event->city?->name,
                    'category' => $event->category?->name,
                    'revenue' => round((float) $paidBookings->sum('total_amount'), 2),
                    'bookings' => $event->bookings->count(),
                    'status' => $event->status,
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentStatusUpdateRequest;
use App\Models\Payment;
use App\Services\Admin\ExportService;
use App\Services\Admin\PaymentAdminService;
use Illuminate\Http\Request;

class PaymentManagementController extends Controller
{
    public function __construct(
        private readonly PaymentAdminService $paymentAdminService,
        private readonly ExportService $exportService
    )
    {
    }

    public function index(Request $request)
    {
        $payments = $this->filteredQuery($request)
            ->latest('paid_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Payment::count(),
            'paid' => Payment::where('status', 'paid')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'refunded' => Payment::where('status', 'refunded')->count(),
            'amount' => Payment::where('status', 'paid')->sum('amount'),
        ];

        $gateways = ['stripe', 'paypal', 'mada', 'cash'];
        $statuses = ['pending', 'paid', 'failed', 'refunded'];

        return view('admin.payments.index', compact('payments', 'stats', 'gateways', 'statuses'));
    }

    public function exportCsv(Request $request)
    {
        [$headers, $rows] = $this->exportDataset($request);

        return $this->exportService->csv('admin-payments.csv', $headers, $rows);
    }

    public function exportXlsx(Request $request)
    {
        [$headers, $rows] = $this->exportDataset($request);

        return $this->exportService->xlsx('admin-payments.xlsx', $headers, $rows);
    }

    public function exportPdf(Request $request)
    {
        $payments = $this->filteredQuery($request)->latest('paid_at')->latest('id')->get();

        return $this->exportService->pdf(
            'admin-payments.pdf',
            'تقرير المدفوعات',
            'تصدير PDF رسمي لعمليات الدفع من لوحة الإدارة.',
            [
                'عدد العمليات' => $payments->count(),
                'الإجمالي المدفوع' => number_format((float) $payments->where('status', 'paid')->sum('amount'), 2).' ر.س',
            ],
            ['المرجع', 'البوابة', 'الحجز', 'العميل', 'الحالة', 'المبلغ', 'التاريخ'],
            $payments->map(fn ($payment) => [
                $payment->transaction_reference,
                strtoupper($payment->gateway),
                $payment->booking?->reference ?? '—',
                $payment->booking?->user?->name ?? '—',
                $payment->status,
                number_format($payment->amount, 2).' '.$payment->currency,
                optional($payment->paid_at)->translatedFormat('d M Y - h:i A'),
            ])->all()
        );
    }

    public function print(Request $request)
    {
        $payments = $this->filteredQuery($request)->latest('paid_at')->latest('id')->get();

        $rows = $payments->map(fn ($payment) => [
            $payment->transaction_reference,
            strtoupper($payment->gateway),
            $payment->booking?->reference ?? '—',
            $payment->booking?->user?->name ?? '—',
            $payment->status,
            number_format($payment->amount, 2).' '.$payment->currency,
            optional($payment->paid_at)->translatedFormat('d M Y - h:i A'),
        ])->all();

        return view('admin.exports.printable', [
            'title' => 'تقرير المدفوعات',
            'subtitle' => 'نسخة جاهزة للطباعة والحفظ بصيغة PDF من المتصفح.',
            'summary' => [
                'عدد العمليات' => $payments->count(),
                'الإجمالي المدفوع' => number_format((float) $payments->where('status', 'paid')->sum('amount'), 2).' ر.س',
            ],
            'headers' => ['المرجع', 'البوابة', 'الحجز', 'العميل', 'الحالة', 'المبلغ', 'التاريخ'],
            'rows' => $rows,
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['booking.user', 'booking.event', 'booking.items.ticket']);
        $statuses = ['pending', 'paid', 'failed', 'refunded'];

        return view('admin.payments.show', compact('payment', 'statuses'));
    }

    public function update(PaymentStatusUpdateRequest $request, Payment $payment)
    {
        $this->paymentAdminService->updateStatus($payment, $request->validated('status'));

        return back()->with('success', 'تم تحديث حالة العملية المالية.');
    }

    private function filteredQuery(Request $request)
    {
        return Payment::with(['booking.user', 'booking.event'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($builder) use ($search) {
                    $builder->where('transaction_reference', 'like', "%{$search}%")
                        ->orWhereHas('booking', fn ($bookingQuery) => $bookingQuery
                            ->where('reference', 'like', "%{$search}%")
                            ->orWhere('customer_email', 'like', "%{$search}%"))
                        ->orWhereHas('booking.user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('gateway'), fn ($query) => $query->where('gateway', $request->string('gateway')->toString()));
    }

    private function exportDataset(Request $request): array
    {
        $headers = [
            'Transaction Reference', 'Gateway', 'Booking Reference', 'Customer', 'Status', 'Amount', 'Currency', 'Paid At',
        ];

        $rows = $this->filteredQuery($request)->latest('paid_at')->latest('id')->get()->map(function ($payment) {
            return [
                $payment->transaction_reference,
                strtoupper($payment->gateway),
                $payment->booking?->reference,
                $payment->booking?->user?->name,
                $payment->status,
                number_format((float) $payment->amount, 2, '.', ''),
                $payment->currency,
                optional($payment->paid_at)->format('Y-m-d H:i'),
            ];
        })->all();

        return [$headers, $rows];
    }
}

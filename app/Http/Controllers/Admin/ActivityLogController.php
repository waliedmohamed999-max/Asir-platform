<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\Admin\ExportService;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(private readonly ExportService $exportService)
    {
    }

    public function index(Request $request)
    {
        $logs = $this->filteredQuery($request)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $users = ActivityLog::query()
            ->with('user:id,name')
            ->whereNotNull('user_id')
            ->latest('id')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $subjectTypes = ActivityLog::query()
            ->whereNotNull('subject_type')
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type');

        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'actors' => ActivityLog::whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];

        return view('admin.activity-logs.index', compact('logs', 'actions', 'users', 'subjectTypes', 'stats'));
    }

    public function exportCsv(Request $request)
    {
        [$headers, $rows] = $this->exportDataset($request);

        return $this->exportService->csv('activity-logs.csv', $headers, $rows);
    }

    public function exportXlsx(Request $request)
    {
        [$headers, $rows] = $this->exportDataset($request);

        return $this->exportService->xlsx('activity-logs.xlsx', $headers, $rows);
    }

    public function exportPdf(Request $request)
    {
        $logs = $this->filteredQuery($request)->latest()->get();

        return $this->exportService->pdf(
            'activity-logs.pdf',
            'سجل النشاطات',
            'تصدير PDF رسمي لسجل النشاطات الإدارية.',
            [
                'عدد السجلات' => $logs->count(),
                'أنواع العمليات' => $logs->pluck('action')->unique()->count(),
            ],
            ['العملية', 'الوصف', 'المستخدم', 'الكيان', 'المعرف', 'IP', 'التاريخ'],
            $logs->map(fn ($log) => [
                $log->action,
                $log->description ?: '—',
                $log->user?->name ?? 'النظام',
                $log->subject_type ? class_basename($log->subject_type) : '—',
                $log->subject_id ?? '—',
                $log->ip_address ?? '—',
                $log->created_at?->translatedFormat('d M Y - h:i A'),
            ])->all()
        );
    }

    public function print(Request $request)
    {
        $logs = $this->filteredQuery($request)->latest()->get();

        return view('admin.exports.printable', [
            'title' => 'سجل النشاطات',
            'subtitle' => 'نسخة جاهزة للطباعة والحفظ بصيغة PDF من المتصفح.',
            'summary' => [
                'عدد السجلات' => $logs->count(),
                'أنواع العمليات' => $logs->pluck('action')->unique()->count(),
            ],
            'headers' => ['العملية', 'الوصف', 'المستخدم', 'الكيان', 'المعرف', 'IP', 'التاريخ'],
            'rows' => $logs->map(fn ($log) => [
                $log->action,
                $log->description ?: '—',
                $log->user?->name ?? 'النظام',
                $log->subject_type ? class_basename($log->subject_type) : '—',
                $log->subject_id ?? '—',
                $log->ip_address ?? '—',
                $log->created_at?->translatedFormat('d M Y - h:i A'),
            ])->all(),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        return ActivityLog::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($builder) use ($search) {
                    $builder->where('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')->toString()))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('subject_type'), fn ($query) => $query->where('subject_type', $request->string('subject_type')->toString()))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('date_to')));
    }

    private function exportDataset(Request $request): array
    {
        $headers = ['Action', 'Description', 'User', 'Subject Type', 'Subject ID', 'IP Address', 'Created At'];

        $rows = $this->filteredQuery($request)->latest()->get()->map(fn ($log) => [
            $log->action,
            $log->description,
            $log->user?->name ?? 'النظام',
            $log->subject_type ? class_basename($log->subject_type) : '—',
            $log->subject_id ?? '—',
            $log->ip_address ?? '—',
            $log->created_at?->format('Y-m-d H:i:s'),
        ])->all();

        return [$headers, $rows];
    }
}

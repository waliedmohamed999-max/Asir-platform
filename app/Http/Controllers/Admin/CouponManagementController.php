<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\Admin\ActivityLogService;
use Illuminate\Http\Request;

class CouponManagementController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index()
    {
        $coupons = Coupon::latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.form', ['coupon' => new Coupon()]);
    }

    public function store(Request $request)
    {
        $coupon = Coupon::create($this->validatedData($request));

        $this->activityLogService->log(
            auth()->id(),
            'coupon.created',
            $coupon,
            "تم إنشاء الكوبون {$coupon->code}",
            ['type' => $coupon->type]
        );

        return redirect()->route('admin.coupons.index')->with('success', 'تم إنشاء الكوبون.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validatedData($request));

        $this->activityLogService->log(
            auth()->id(),
            'coupon.updated',
            $coupon,
            "تم تحديث الكوبون {$coupon->code}",
            ['type' => $coupon->type]
        );

        return back()->with('success', 'تم تحديث الكوبون.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:fixed,percentage'],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResaleListing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResaleListingManagementController extends Controller
{
    public function index(Request $request)
    {
        $listings = ResaleListing::with(['event.city', 'ticket', 'seller', 'buyer', 'bookingItem.booking'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search) {
                    $builder->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('event', fn ($event) => $event->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('seller', fn ($seller) => $seller->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => ResaleListing::count(),
            'active' => ResaleListing::where('status', ResaleListing::STATUS_ACTIVE)->count(),
            'sold' => ResaleListing::where('status', ResaleListing::STATUS_SOLD)->count(),
            'value' => ResaleListing::where('status', ResaleListing::STATUS_ACTIVE)->sum('price'),
        ];

        $statuses = [
            ResaleListing::STATUS_ACTIVE,
            ResaleListing::STATUS_PENDING,
            ResaleListing::STATUS_SOLD,
            ResaleListing::STATUS_CANCELLED,
            ResaleListing::STATUS_EXPIRED,
        ];

        return view('admin.resale-listings.index', compact('listings', 'stats', 'statuses'));
    }

    public function update(Request $request, ResaleListing $resaleListing)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                ResaleListing::STATUS_ACTIVE,
                ResaleListing::STATUS_PENDING,
                ResaleListing::STATUS_SOLD,
                ResaleListing::STATUS_CANCELLED,
                ResaleListing::STATUS_EXPIRED,
            ])],
        ]);

        $payload = ['status' => $validated['status']];

        if ($validated['status'] === ResaleListing::STATUS_SOLD && ! $resaleListing->sold_at) {
            $payload['sold_at'] = now();
        }

        $resaleListing->update($payload);

        return back()->with('success', 'تم تحديث حالة قائمة إعادة البيع.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CityUpsertRequest;
use App\Models\City;
use App\Services\Admin\ActivityLogService;

class CityManagementController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index()
    {
        $cities = City::orderBy('sort_order')->latest()->paginate(20);

        return view('admin.cities.index', compact('cities'));
    }

    public function create()
    {
        return view('admin.cities.form', ['city' => new City()]);
    }

    public function store(CityUpsertRequest $request)
    {
        $city = City::create($request->payload());

        $this->activityLogService->log(
            auth()->id(),
            'city.created',
            $city,
            "تم إنشاء المدينة {$city->name}",
            ['slug' => $city->slug]
        );

        return redirect()->route('admin.cities.index')->with('success', 'تم إنشاء المدينة بنجاح.');
    }

    public function edit(City $city)
    {
        return view('admin.cities.form', compact('city'));
    }

    public function update(CityUpsertRequest $request, City $city)
    {
        $city->update($request->payload());

        $this->activityLogService->log(
            auth()->id(),
            'city.updated',
            $city,
            "تم تحديث المدينة {$city->name}",
            ['slug' => $city->slug]
        );

        return back()->with('success', 'تم تحديث بيانات المدينة.');
    }
}

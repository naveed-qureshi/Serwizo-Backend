<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::with('city');

        if ($cityId = $request->query('city_id')) {
            $query->where('city_id', $cityId);
        }

        $areas = $query->orderBy('name')->paginate(15)->withQueryString();
        $cities = City::where('status', 'active')->get();

        return view('areas.index', compact('areas', 'cities'));
    }

    public function create()
    {
        $cities = City::where('status', 'active')->get();
        return view('areas.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:150'],
            'zone' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $area = Area::create($validated);

        AuditLogService::log(
            action: 'created',
            module: 'cities',
            recordId: $area->id,
            description: "Created area: {$area->name} in {$area->city->name}"
        );

        return redirect()->route('areas.index')->with('success', 'Area created successfully.');
    }

    public function edit(Area $area)
    {
        $cities = City::where('status', 'active')->get();
        return view('areas.edit', compact('area', 'cities'));
    }

    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:150'],
            'zone' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $area->update($validated);

        AuditLogService::log(
            action: 'updated',
            module: 'cities',
            recordId: $area->id,
            description: "Updated area: {$area->name}"
        );

        return redirect()->route('areas.index')->with('success', 'Area updated successfully.');
    }

    public function getByCity(City $city)
    {
        return response()->json([
            'success' => true,
            'data' => $city->areas()->where('status', 'active')->get(['id', 'name']),
        ]);
    }
}

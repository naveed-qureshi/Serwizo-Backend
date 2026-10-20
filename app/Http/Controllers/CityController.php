<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::withCount(['areas', 'workers', 'serviceRequests'])
            ->orderBy('name')
            ->paginate(15);

        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        return view('cities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:cities,name'],
            'province' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $city = City::create($validated);

        AuditLogService::log(
            action: 'created',
            module: 'cities',
            recordId: $city->id,
            description: "Created city: {$city->name}"
        );

        return redirect()->route('cities.index')->with('success', 'City added successfully.');
    }

    public function edit(City $city)
    {
        return view('cities.edit', compact('city'));
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('cities', 'name')->ignore($city->id)],
            'province' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $city->update($validated);

        AuditLogService::log(
            action: 'updated',
            module: 'cities',
            recordId: $city->id,
            description: "Updated city: {$city->name}"
        );

        return redirect()->route('cities.index')->with('success', 'City updated successfully.');
    }
}

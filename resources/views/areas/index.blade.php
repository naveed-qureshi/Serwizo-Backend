@extends('layouts.app')

@section('title', 'Areas & Zones')
@section('header_title', 'Area & Zone Management')
@section('header_subtitle', 'Sub-city operational zones linked to master cities')

@section('content')
<div class="space-y-6 pt-4">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-2">
            <a href="{{ route('cities.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">Cities</a>
            <a href="{{ route('areas.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white">Areas / Zones</a>
        </div>

        <form method="GET" action="{{ route('areas.index') }}" class="flex items-center space-x-2 text-xs">
            <select name="city_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            <a href="{{ route('areas.create') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition whitespace-nowrap">
                <i class="fa-solid fa-plus mr-1"></i> Add Area
            </a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                <tr>
                    <th class="py-3.5 px-4">Area / Sector Name</th>
                    <th class="py-3.5 px-4">City</th>
                    <th class="py-3.5 px-4">Zone</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($areas as $area)
                <tr class="hover:bg-slate-50/80">
                    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $area->name }}</td>
                    <td class="py-3.5 px-4 font-medium text-slate-800">{{ $area->city?->name }}</td>
                    <td class="py-3.5 px-4 text-slate-500">{{ $area->zone ?? 'Standard' }}</td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $area->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                            {{ $area->status }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <a href="{{ route('areas.edit', $area) }}" class="p-1.5 text-slate-600 hover:text-blue-600 rounded-md hover:bg-slate-100 transition inline-block">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-6 text-center text-slate-400">No areas added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($areas->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $areas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Cities')
@section('header_title', 'City Management')
@section('header_subtitle', 'Nationwide Operating Cities & Regional Service Hubs')

@section('content')
<div class="space-y-6 pt-4">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
            <i class="fa-solid fa-city text-emerald-600"></i>
            <span>Active Operating Cities</span>
        </h3>
        <a href="{{ route('cities.create') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i class="fa-solid fa-plus mr-1"></i> Add City
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                <tr>
                    <th class="py-3.5 px-4">City Name</th>
                    <th class="py-3.5 px-4">Province</th>
                    <th class="py-3.5 px-4">Registered Workers</th>
                    <th class="py-3.5 px-4">Total Requests</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($cities as $city)
                <tr class="hover:bg-slate-50/80">
                    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $city->name }}</td>
                    <td class="py-3.5 px-4 text-slate-600">{{ $city->province ?? '-' }}</td>
                    <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $city->workers_count }} Workers</td>
                    <td class="py-3.5 px-4 text-slate-700">{{ $city->service_requests_count }} Jobs</td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $city->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                            {{ $city->status }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <a href="{{ route('cities.edit', $city) }}" class="p-1.5 text-slate-600 hover:text-blue-600 rounded-md hover:bg-slate-100 transition inline-block">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-6 text-center text-slate-400">No cities added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($cities->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $cities->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

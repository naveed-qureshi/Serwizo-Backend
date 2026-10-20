@extends('layouts.app')

@section('title', 'Job Operations Report')
@section('header_title', 'Job Operations Report')
@section('header_subtitle', 'Comprehensive Dispatch Pipeline & Execution Performance')

@section('content')
<div class="space-y-6 pt-4 text-xs">
    
    <!-- Filter -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('reports.jobs') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div>
                <label class="font-semibold text-slate-600 block mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            </div>

            <div>
                <label class="font-semibold text-slate-600 block mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            </div>

            <div>
                <label class="font-semibold text-slate-600 block mb-1">City</label>
                <select name="city_id" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
                    <option value="">All Cities</option>
                    @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold">Filter Report</button>
                <button type="button" onclick="window.print()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold"><i class="fa-solid fa-print"></i></button>
            </div>
        </form>
    </div>

    <!-- Jobs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4">SR Number</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Job Service</th>
                        <th class="py-3 px-4">Worker</th>
                        <th class="py-3 px-4">City</th>
                        <th class="py-3 px-4 text-right">Price</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jobs as $job)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3 px-4 font-bold">
                            <a href="{{ route('service-requests.show', $job) }}" class="text-slate-900 hover:text-emerald-600">
                                {{ $job->sr_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-slate-500">{{ $job->created_at->format('M d, Y') }}</td>
                        <td class="py-3 px-4">{{ $job->customer?->name }}</td>
                        <td class="py-3 px-4 font-medium">{{ $job->serviceJob?->title }}</td>
                        <td class="py-3 px-4">{{ $job->currentWorker?->name ?? 'Unassigned' }}</td>
                        <td class="py-3 px-4">{{ $job->city?->name }}</td>
                        <td class="py-3 px-4 text-right font-bold">Rs. {{ number_format($job->customer_price, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $job->status_badge_color }}">
                                {{ strtoupper(str_replace('_', ' ', $job->status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-6 text-center text-slate-400">No jobs found in selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $jobs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

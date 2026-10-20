@extends('layouts.app')

@section('title', 'Revenue Report')
@section('header_title', 'Financial Revenue & Margin Report')
@section('header_subtitle', 'Customer Revenue, Worker Payouts & SerWizo Gross Margin Analytics')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Date Filter -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
        <h3 class="font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
            <i class="fa-solid fa-chart-line text-emerald-600"></i>
            <span>Reporting Period</span>
        </h3>

        <form method="GET" action="{{ route('reports.revenue') }}" class="flex items-center space-x-2">
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            <span class="text-slate-400">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold">Generate</button>
            <button type="button" onclick="window.print()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold">
                <i class="fa-solid fa-print"></i>
            </button>
        </form>
    </div>

    <!-- Executive Totals Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Gross Customer Revenue</span>
            <div class="text-2xl font-black text-slate-900 mt-1">Rs. {{ number_format($totalCustomerRevenue, 2) }}</div>
            <span class="text-[11px] text-slate-400">Total volume billed across completed jobs</span>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Technician Wages (COGS)</span>
            <div class="text-2xl font-black text-blue-600 mt-1">Rs. {{ number_format($totalWorkerPayout, 2) }}</div>
            <span class="text-[11px] text-slate-400">Direct labor costs disbursed/payable</span>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">SerWizo Net Margin</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">Rs. {{ number_format($totalCommission, 2) }}</div>
            <span class="text-[11px] text-emerald-600 font-semibold">
                @if($totalCustomerRevenue > 0)
                {{ number_format(($totalCommission / $totalCustomerRevenue) * 100, 1) }}% Gross Operating Margin
                @else
                0% Margin
                @endif
            </span>
        </div>
    </div>

    <!-- Breakdown Grids -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        
        <!-- City-wise Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 font-bold uppercase tracking-wider text-slate-800">
                City-Wise Revenue Breakdown
            </div>
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-4">City</th>
                        <th class="py-2.5 px-4 text-center">Jobs</th>
                        <th class="py-2.5 px-4 text-right">Revenue</th>
                        <th class="py-2.5 px-4 text-right">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cityRevenue as $cRev)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-2.5 px-4 font-bold text-slate-800">{{ $cRev['city_name'] }}</td>
                        <td class="py-2.5 px-4 text-center">{{ $cRev['jobs_count'] }}</td>
                        <td class="py-2.5 px-4 text-right font-semibold">Rs. {{ number_format($cRev['revenue'], 2) }}</td>
                        <td class="py-2.5 px-4 text-right font-black text-emerald-600">Rs. {{ number_format($cRev['commission'], 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">No completed jobs in selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Category-wise Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 font-bold uppercase tracking-wider text-slate-800">
                Category-Wise Revenue Breakdown
            </div>
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-4">Category</th>
                        <th class="py-2.5 px-4 text-center">Jobs</th>
                        <th class="py-2.5 px-4 text-right">Revenue</th>
                        <th class="py-2.5 px-4 text-right">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categoryRevenue as $catRev)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-2.5 px-4 font-bold text-slate-800">{{ $catRev['category_name'] }}</td>
                        <td class="py-2.5 px-4 text-center">{{ $catRev['jobs_count'] }}</td>
                        <td class="py-2.5 px-4 text-right font-semibold">Rs. {{ number_format($catRev['revenue'], 2) }}</td>
                        <td class="py-2.5 px-4 text-right font-black text-emerald-600">Rs. {{ number_format($catRev['commission'], 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">No completed jobs in selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection

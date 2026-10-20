@extends('layouts.app')

@section('title', $brand->name . ' - Brand Portal')
@section('header_title', $brand->name . ' Partner Portal')
@section('header_subtitle', 'Authorized Brand Service Dashboard & Warranty Installation Tracking')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Brand Welcome Banner -->
    <div class="bg-gradient-to-r from-navy-900 via-slate-900 to-emerald-950 p-6 rounded-2xl shadow-lg text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="inline-flex items-center space-x-2 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[11px] font-bold uppercase tracking-wider">
                <i class="fa-solid fa-certificate"></i>
                <span>Official Partner Account</span>
            </div>
            <h2 class="text-xl font-black">{{ $brand->name }} Installation Operations</h2>
            <p class="text-xs text-slate-300">Live service tracking, customer details, proof photos, and account statement.</p>
        </div>

        <div class="text-right shrink-0">
            <span class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Current Balance Payable</span>
            <div class="text-2xl font-black text-amber-400">Rs. {{ number_format($currentBalance, 2) }}</div>
            <a href="{{ route('brand-portal.ledger') }}" class="text-[11px] text-emerald-400 hover:underline font-semibold mt-1 inline-block">View Full Statement &rarr;</a>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Installations</span>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ $totalJobs }}</div>
            <span class="text-[11px] text-slate-400">All registered jobs</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Completed & Verified</span>
            <div class="mt-2 text-2xl font-black text-emerald-600">{{ $completedJobs }}</div>
            <span class="text-[11px] text-slate-400">Successfully executed</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">In Progress / Pending</span>
            <div class="mt-2 text-2xl font-black text-amber-600">{{ $pendingJobs }}</div>
            <span class="text-[11px] text-slate-400">Active in field</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Paid to SerWizo</span>
            <div class="mt-2 text-2xl font-black text-blue-600">Rs. {{ number_format($totalPaid, 2) }}</div>
            <span class="text-[11px] text-slate-400">Settled invoices</span>
        </div>
    </div>

    <!-- Recent Installations -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                <span>Recent {{ $brand->name }} Service Jobs</span>
            </h4>
            <a href="{{ route('brand-portal.jobs') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">View All Jobs &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                        <th class="py-2.5 px-4">Job ID</th>
                        <th class="py-2.5 px-4">Date</th>
                        <th class="py-2.5 px-4">Customer</th>
                        <th class="py-2.5 px-4">City</th>
                        <th class="py-2.5 px-4">Model & Serial</th>
                        <th class="py-2.5 px-4">Duration</th>
                        <th class="py-2.5 px-4">Rate</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentJobs as $job)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <a href="{{ route('brand-portal.jobs.show', $job) }}" class="hover:text-emerald-600">
                                {{ $job->sr_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">{{ $job->created_at->format('Y-m-d') }}</td>
                        <td class="py-3 px-4 text-slate-800">
                            <div class="font-bold">{{ $job->customer?->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $job->customer?->phone }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $job->city?->name }}</td>
                        <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                            @if($job->model_number) <div>Mod: {{ $job->model_number }}</div> @endif
                            @if($job->serial_number) <div>S/N: {{ $job->serial_number }}</div> @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-semibold">{{ $job->formatted_duration }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900">Rs. {{ number_format($job->brand_rate ?: $job->customer_price, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $job->status_badge_color }}">
                                {{ strtoupper(str_replace('_', ' ', $job->status)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('brand-portal.jobs.show', $job) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                                Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">No installations recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

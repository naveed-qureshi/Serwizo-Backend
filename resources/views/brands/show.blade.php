@extends('layouts.app')

@section('title', $brand->name)
@section('header_title', $brand->name)
@section('header_subtitle', 'Brand Overview, Receivable Balance & Service Records')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('brands.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Back to Brands List
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('brand-ledger.index', ['brand_id' => $brand->id]) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-file-invoice-dollar mr-1.5"></i>
                <span>Open Brand Ledger</span>
            </a>
            <a href="{{ route('brands.edit', $brand) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-pen-to-square mr-1.5"></i>
                <span>Edit Brand</span>
            </a>
        </div>
    </div>

    <!-- Top KPI Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Receivable Balance</span>
            <div class="mt-2 text-2xl font-black {{ $ledgerBalance > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                Rs. {{ number_format($ledgerBalance, 2) }}
            </div>
            <span class="text-[11px] text-slate-400">Net unpaid billings</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Associated Services</span>
            <div class="mt-2 text-2xl font-black text-indigo-600">
                {{ $brand->serviceJobs->count() }}
            </div>
            <span class="text-[11px] text-slate-400">Custom job titles & rates</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Service Jobs</span>
            <div class="mt-2 text-2xl font-black text-blue-600">
                {{ $brand->service_requests_count }}
            </div>
            <span class="text-[11px] text-slate-400">All recorded installations</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Status</span>
            <div class="mt-2 flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded text-xs font-bold uppercase {{ $brand->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $brand->status }}
                </span>
            </div>
            <span class="text-[11px] text-slate-400 font-mono">Code: {{ $brand->code }}</span>
        </div>
    </div>

    <!-- Brand Profile & Portal Users -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 space-y-3 text-xs">
            <h4 class="font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Contact & Operations</h4>
            <div class="space-y-2">
                <div>
                    <span class="text-slate-400 block font-medium">Contact Person:</span>
                    <span class="text-slate-800 font-bold">{{ $brand->contact_person ?: 'Not specified' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Phone Number:</span>
                    <span class="text-slate-800 font-semibold">{{ $brand->phone ?: 'Not specified' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Official Email:</span>
                    <span class="text-slate-800">{{ $brand->email ?: 'Not specified' }}</span>
                </div>
                @if($brand->notes)
                <div>
                    <span class="text-slate-400 block font-medium">Notes:</span>
                    <p class="text-slate-600 italic">{{ $brand->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="md:col-span-2 bg-white p-5 rounded-xl shadow-sm border border-slate-200 text-xs">
            <h4 class="font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2 mb-3 flex items-center justify-between">
                <span>Associated Brand Portal Logins</span>
                <span class="text-slate-400 font-normal">({{ $brandUsers->count() }} accounts)</span>
            </h4>

            <div class="divide-y divide-slate-100">
                @forelse($brandUsers as $user)
                <div class="py-2 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-900">{{ $user->name }}</div>
                        <div class="text-slate-500">{{ $user->email }}</div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                        {{ $user->status }}
                    </span>
                </div>
                @empty
                <div class="py-4 text-center text-slate-400">
                    No brand portal user account assigned yet. You can create one from the Users section or when creating a brand.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Associated Services & Job Rates for this Brand -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                    <i class="fa-solid fa-tags text-indigo-600"></i>
                    <span>Associated Catalog Services & Negotiated Rates ({{ $brand->serviceJobs->count() }})</span>
                </h4>
                <p class="text-[11px] text-slate-500 mt-0.5">Each service title has its own specific corporate billing rate and technician wage.</p>
            </div>
            <a href="{{ route('jobs.create') }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition">
                <i class="fa-solid fa-plus mr-1"></i> Add Service
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                        <th class="py-2.5 px-4">Service Title</th>
                        <th class="py-2.5 px-4">Category</th>
                        <th class="py-2.5 px-4 text-right">Brand Rate (Billed)</th>
                        <th class="py-2.5 px-4 text-right">Worker Wage (Paid)</th>
                        <th class="py-2.5 px-4 text-right">SerWizo Margin</th>
                        <th class="py-2.5 px-4">Type</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brand->serviceJobs as $sJob)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <div>{{ $sJob->title }}</div>
                            <span class="text-[11px] font-mono text-slate-400">{{ $sJob->code ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-700">
                            {{ $sJob->category?->title }}
                            @if($sJob->subCategory)
                            <span class="text-[11px] text-slate-400 block">{{ $sJob->subCategory->title }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-black text-amber-600">
                            Rs. {{ number_format($sJob->brand_rate, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-blue-600">
                            Rs. {{ number_format($sJob->default_worker_payment, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-black text-emerald-600">
                            Rs. {{ number_format(max(0, $sJob->brand_rate - $sJob->default_worker_payment), 2) }}
                        </td>
                        <td class="py-3 px-4">
                            @if($sJob->is_free_installation)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase">
                                Free Install
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                Cash / Paid
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $sJob->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                {{ $sJob->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('jobs.edit', $sJob) }}" class="p-1.5 text-slate-600 hover:text-indigo-600 rounded-md hover:bg-slate-100 transition inline-block font-semibold">
                                <i class="fa-regular fa-pen-to-square mr-1"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-slate-400">
                            No catalog service jobs associated with {{ $brand->name }} yet. 
                            <a href="{{ route('jobs.create') }}" class="text-indigo-600 hover:underline font-semibold ml-1">Create one now &rarr;</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Brand Jobs -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                <span>Recent {{ $brand->name }} Installation Jobs</span>
            </h4>
            <a href="{{ route('service-requests.index', ['search' => $brand->name]) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">View in Requests &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                        <th class="py-2.5 px-4">SR Number</th>
                        <th class="py-2.5 px-4">Customer</th>
                        <th class="py-2.5 px-4">Model & Serial</th>
                        <th class="py-2.5 px-4">Worker</th>
                        <th class="py-2.5 px-4">Duration</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentJobs as $job)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <a href="{{ route('service-requests.show', $job) }}" class="hover:text-emerald-600">
                                {{ $job->sr_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-slate-700">
                            <div class="font-medium">{{ $job->customer?->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $job->customer?->phone }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                            @if($job->model_number) <div>Model: {{ $job->model_number }}</div> @endif
                            @if($job->serial_number) <div>S/N: {{ $job->serial_number }}</div> @endif
                            @if(!$job->model_number && !$job->serial_number) <span class="text-slate-400">-</span> @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $job->currentWorker?->name ?? 'Unassigned' }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $job->formatted_duration }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $job->status_badge_color }}">
                                {{ strtoupper(str_replace('_', ' ', $job->status)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('service-requests.show', $job) }}" class="text-slate-500 hover:text-slate-900">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-400">No service jobs logged for this brand yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

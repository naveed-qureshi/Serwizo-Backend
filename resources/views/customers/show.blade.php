@extends('layouts.app')

@section('title', 'Customer - ' . $customer->name)
@section('header_title', 'Customer Profile: ' . $customer->name)
@section('header_subtitle', 'Customer Code: ' . $customer->customer_code . ' &bull; ' . $customer->phone)

@section('content')
<div class="space-y-6 pt-4">
    <!-- Customer Profile Info -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xl">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $customer->name }}</h2>
                    <span class="text-xs text-slate-500 font-mono font-semibold">{{ $customer->customer_code }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600 font-medium">
                <span><i class="fa-solid fa-phone mr-1 text-slate-400"></i> {{ $customer->phone }}</span>
                @if($customer->alt_phone)
                <span><i class="fa-brands fa-whatsapp mr-1 text-emerald-500"></i> {{ $customer->alt_phone }}</span>
                @endif
                @if($customer->email)
                <span><i class="fa-regular fa-envelope mr-1 text-slate-400"></i> {{ $customer->email }}</span>
                @endif
                <span><i class="fa-solid fa-location-dot mr-1 text-slate-400"></i> {{ $customer->city?->name }}</span>
            </div>

            <div class="flex items-center space-x-3">
                <p class="text-xs text-slate-500">{{ $customer->address }}</p>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($customer->address . ', ' . ($customer->city?->name ?? '') . ', Pakistan') }}" 
                   target="_blank" 
                   class="inline-flex items-center space-x-1 text-[11px] text-emerald-700 hover:text-emerald-800 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 transition">
                    <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                    <span>Map</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                </a>
            </div>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('customers.edit', $customer) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-lg text-xs transition">
                <i class="fa-regular fa-pen-to-square mr-1"></i> Edit Customer
            </a>
            <a href="{{ route('service-requests.create', ['customer_phone' => $customer->phone]) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-sm">
                <i class="fa-solid fa-plus mr-1"></i> New Request
            </a>
        </div>
    </div>

    <!-- Lifetime Service History -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                <span>Complete Service Request History ({{ $customer->serviceRequests->count() }})</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                    <tr>
                        <th class="py-3 px-4">SR Number</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Job Service</th>
                        <th class="py-3 px-4">Assigned Worker</th>
                        <th class="py-3 px-4">City</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->serviceRequests as $sr)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3 px-4 font-bold">
                            <a href="{{ route('service-requests.show', $sr) }}" class="text-slate-900 hover:text-emerald-600">
                                {{ $sr->sr_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-slate-500">{{ $sr->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-3 px-4 font-medium text-slate-800">{{ $sr->serviceJob?->title }}</td>
                        <td class="py-3 px-4">
                            @if($sr->currentWorker)
                            <a href="{{ route('workers.show', $sr->currentWorker) }}" class="font-medium text-slate-700 hover:text-emerald-600">
                                {{ $sr->currentWorker->name }}
                            </a>
                            @else
                            <span class="text-slate-400">Unassigned</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $sr->city?->name }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">Rs. {{ number_format($sr->customer_price, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $sr->status_badge_color }}">
                                {{ strtoupper(str_replace('_', ' ', $sr->status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-400">No service requests recorded for this customer yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

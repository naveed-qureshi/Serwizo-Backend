@extends('layouts.app')

@section('title', 'Service Requests')
@section('header_title', 'Service Request Dispatch Hub')
@section('header_subtitle', 'Live Operational Pipeline & Nationwide Dispatch Queue')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Filters & Action Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-filter text-emerald-600"></i>
                <span>Filter Service Requests</span>
            </h3>

            <a href="{{ route('service-requests.create') }}" 
               class="inline-flex items-center justify-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition whitespace-nowrap">
                <i class="fa-solid fa-phone-volume"></i>
                <span>Book New Request</span>
            </a>
        </div>

        <form method="GET" action="{{ route('service-requests.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2.5 text-xs">
            <!-- Search -->
            <div class="relative lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search SR #, phone, customer..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
            </div>

            <!-- Status -->
            <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700">
                <option value="">All Statuses</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                <option value="pending_assignment" {{ request('status') == 'pending_assignment' ? 'selected' : '' }}>Pending Assignment</option>
                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="on_the_way" {{ request('status') == 'on_the_way' ? 'selected' : '' }}>On The Way</option>
                <option value="arrived" {{ request('status') == 'arrived' ? 'selected' : '' }}>Arrived</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <!-- City -->
            <select name="city_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>

            <!-- Category -->
            <select name="category_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                @endforeach
            </select>

            <div class="flex items-center space-x-1.5">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-2 py-2 bg-slate-50 border border-slate-300 rounded-lg text-[11px]" title="From Date">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold shrink-0">Filter</button>
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                    <tr>
                        <th class="py-3.5 px-4">SR Number & Date</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Job Service</th>
                        <th class="py-3.5 px-4">City</th>
                        <th class="py-3.5 px-4">Assigned Worker</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Amount</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($serviceRequests as $sr)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <a href="{{ route('service-requests.show', $sr) }}" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                {{ $sr->sr_number }}
                            </a>
                            <span class="text-[11px] text-slate-400 font-medium">{{ $sr->created_at->format('M d, Y H:i') }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('customers.show', $sr->customer) }}" class="font-semibold text-slate-800 hover:text-emerald-600 block">
                                {{ $sr->customer?->name }}
                            </a>
                            <span class="text-[11px] text-slate-500 font-mono">{{ $sr->customer?->phone }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-slate-900">{{ $sr->serviceJob?->title }}</div>
                            <span class="text-[10px] text-slate-500">{{ $sr->category?->title }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-semibold text-slate-800">{{ $sr->city?->name }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($sr->currentWorker)
                            <a href="{{ route('workers.show', $sr->currentWorker) }}" class="font-medium text-slate-800 hover:text-emerald-600 flex items-center space-x-1.5">
                                <span>{{ $sr->currentWorker->name }}</span>
                            </a>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $sr->currentWorker->worker_code }}</span>
                            @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Unassigned
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $sr->status_badge_color }}">
                                {{ strtoupper(str_replace('_', ' ', $sr->status)) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="font-bold text-slate-900">Rs. {{ number_format($sr->customer_price, 2) }}</div>
                            <span class="text-[10px] text-slate-400">Wage: Rs. {{ number_format($sr->worker_payment, 2) }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('service-requests.edit', $sr) }}" 
                                   class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 rounded-lg text-xs transition" title="Edit Request">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <a href="{{ route('service-requests.show', $sr) }}" 
                                   class="px-2.5 py-1.5 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 font-semibold rounded-lg text-xs transition inline-flex items-center space-x-1">
                                    <span>Manage</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">No service requests found matching your filter criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($serviceRequests->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $serviceRequests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

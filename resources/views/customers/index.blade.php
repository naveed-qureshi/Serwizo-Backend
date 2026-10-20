@extends('layouts.app')

@section('title', 'Customers')
@section('header_title', 'Customer Management')
@section('header_subtitle', 'Verified Clients, Service Requests & Lifetime Service History')

@section('content')
<div class="space-y-6 pt-4">
    <!-- Filter and Action Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('customers.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search phone, name, customer code..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
            </div>

            <select name="city_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-700">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>

            <div>
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
            </div>
        </form>

        <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition whitespace-nowrap">
            <i class="fa-solid fa-user-plus mr-1"></i> Add Customer
        </a>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                    <tr>
                        <th class="py-3.5 px-4">Customer Details</th>
                        <th class="py-3.5 px-4">Phone Number</th>
                        <th class="py-3.5 px-4">City</th>
                        <th class="py-3.5 px-4">Address</th>
                        <th class="py-3.5 px-4">Total Requests</th>
                        <th class="py-3.5 px-4">Completed</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3.5 px-4">
                            <a href="{{ route('customers.show', $customer) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                {{ $customer->name }}
                            </a>
                            <span class="block text-[11px] text-slate-500 font-mono">{{ $customer->customer_code }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">{{ $customer->phone }}</td>
                        <td class="py-3.5 px-4 font-medium text-slate-700">{{ $customer->city?->name ?? 'N/A' }}</td>
                        <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate">{{ $customer->address ?? '-' }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">{{ $customer->service_requests_count }}</td>
                        <td class="py-3.5 px-4 font-bold text-emerald-600">{{ $customer->completed_requests_count }}</td>
                        <td class="py-3.5 px-4 text-right space-x-1">
                            <a href="{{ route('customers.show', $customer) }}" class="p-1.5 text-slate-600 hover:text-emerald-600 rounded-md hover:bg-slate-100 transition inline-block">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="{{ route('customers.edit', $customer) }}" class="p-1.5 text-slate-600 hover:text-blue-600 rounded-md hover:bg-slate-100 transition inline-block">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-400">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Workers')
@section('header_title', 'Worker Management')
@section('header_subtitle', 'Registered Technicians, Service Areas, Availability & Verification')

@section('content')
<div class="space-y-6 pt-4">
    
    <!-- Action and Search Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('workers.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, CNIC, code..."
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

            <select name="category_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-700">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                @endforeach
            </select>

            <div class="flex items-center space-x-2">
                <select name="availability" class="flex-1 px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-700">
                    <option value="">Availability</option>
                    <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="busy" {{ request('availability') == 'busy' ? 'selected' : '' }}>Busy</option>
                    <option value="on_leave" {{ request('availability') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                    <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                </select>
                <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
            </div>
        </form>

        <a href="{{ route('workers.create') }}" 
           class="inline-flex items-center justify-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition whitespace-nowrap">
            <i class="fa-solid fa-user-plus"></i>
            <span>Register New Worker</span>
        </a>
    </div>

    <!-- Workers Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Worker Details</th>
                        <th class="py-3.5 px-4">Phone & CNIC</th>
                        <th class="py-3.5 px-4">City / Service Area</th>
                        <th class="py-3.5 px-4">Categories & Skill</th>
                        <th class="py-3.5 px-4">Status & Availability</th>
                        <th class="py-3.5 px-4">Registered</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workers as $worker)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden shrink-0 border border-slate-300">
                                    @if($worker->photo_path)
                                    <img src="{{ $worker->photo_url }}" alt="{{ $worker->name }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'font-bold text-xs text-slate-600\'>{{ strtoupper(substr($worker->name, 0, 1)) }}</span>';">
                                    @else
                                    <i class="fa-solid fa-user text-slate-400"></i>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('workers.show', $worker) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                        {{ $worker->name }}
                                    </a>
                                    <span class="block text-[11px] text-slate-500 font-mono">{{ $worker->worker_code }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-slate-800">{{ $worker->phone }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $worker->cnic_number }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-slate-800">{{ $worker->primaryCity?->name }}</span>
                            @if($worker->cities->count() > 1)
                            <span class="text-[10px] text-slate-500 block">+ {{ $worker->cities->count() - 1 }} other cities</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex flex-wrap gap-1 mb-0.5">
                                @foreach($worker->categories as $cat)
                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-medium">
                                    {{ $cat->title }}
                                </span>
                                @endforeach
                            </div>
                            <span class="text-[10px] text-emerald-600 font-semibold uppercase">{{ $worker->skill_level }} ({{ $worker->experience_years }} yrs)</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="space-y-1">
                                <div>
                                    @if($worker->status === 'active')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">ACTIVE</span>
                                    @elseif($worker->status === 'suspended')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">SUSPENDED</span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200">INACTIVE</span>
                                    @endif
                                </div>
                                <span class="text-[10px] font-medium text-slate-500 capitalize">
                                    <i class="fa-solid fa-circle text-[8px] mr-1 {{ $worker->availability === 'available' ? 'text-emerald-500' : ($worker->availability === 'busy' ? 'text-amber-500' : 'text-slate-400') }}"></i>
                                    {{ str_replace('_', ' ', $worker->availability) }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $worker->created_at->format('M d, Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('workers.show', $worker) }}" title="View Profile" 
                               class="p-1.5 text-slate-600 hover:text-emerald-600 rounded-md hover:bg-slate-100 transition inline-block">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="{{ route('workers.edit', $worker) }}" title="Edit Worker" 
                               class="p-1.5 text-slate-600 hover:text-blue-600 rounded-md hover:bg-slate-100 transition inline-block">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            @if(auth()->user()->isAccounts())
                            <a href="{{ route('ledger.show', $worker) }}" title="View Ledger" 
                               class="p-1.5 text-slate-600 hover:text-amber-600 rounded-md hover:bg-slate-100 transition inline-block">
                                <i class="fa-solid fa-book-journal-whills"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No workers found matching the filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($workers->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $workers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

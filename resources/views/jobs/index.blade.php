@extends('layouts.app')

@section('title', 'Job Services Catalog')
@section('header_title', 'Job Services Catalog')
@section('header_subtitle', 'Master Standardized Services, Pricing & Commission Structure')

@section('content')
<div class="space-y-6 pt-4">
    <!-- Filter and Action Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('jobs.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or code..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
            </div>

            <select name="category_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-700">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                @endforeach
            </select>

            <div class="flex items-center space-x-2">
                <select name="status" class="flex-1 px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-700 font-medium">
                    <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active Jobs Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Jobs</option>
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                </select>
                <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
            </div>
        </form>

        <a href="{{ route('jobs.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> New Service Title
        </a>
    </div>

    <!-- Catalog Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                    <tr>
                        <th class="py-3.5 px-4">Service Title & Code</th>
                        <th class="py-3.5 px-4">Category & Subcategory</th>
                        <th class="py-3.5 px-4">Duration</th>
                        <th class="py-3.5 px-4 text-right">Customer Price</th>
                        <th class="py-3.5 px-4 text-right">Worker Payment</th>
                        <th class="py-3.5 px-4 text-right">SerWizo Commission</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jobs as $job)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $job->title }}</div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-[11px] text-slate-500 font-mono">{{ $job->code ?? 'N/A' }}</span>
                                @if($job->is_brand_job)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                    <i class="fa-solid fa-building mr-0.5 text-[9px]"></i> {{ $job->brand?->name }}
                                </span>
                                @if($job->is_free_installation)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Free Install
                                </span>
                                @endif
                                @else
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                    Cash Job
                                </span>
                                @endif
                                @if($job->is_promotional)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200">
                                    <i class="fa-solid fa-tag mr-0.5"></i> Promo {{ $job->discount_percentage ? "({$job->discount_percentage}% OFF)" : '' }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-semibold text-slate-800">{{ $job->category?->title }}</span>
                            <span class="text-[11px] text-slate-500 block">{{ $job->subCategory?->title ?? 'General' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                            <i class="fa-regular fa-clock text-slate-400 mr-1"></i> {{ $job->estimated_duration_minutes }} mins
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($job->is_promotional && $job->promotional_customer_price !== null)
                                <div class="font-bold text-rose-600">Rs. {{ number_format($job->promotional_customer_price, 2) }}</div>
                                <div class="text-[10px] text-slate-400 line-through">Rs. {{ number_format($job->default_customer_price, 2) }}</div>
                            @else
                                <div class="font-bold text-slate-900">Rs. {{ number_format($job->default_customer_price, 2) }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($job->is_promotional && $job->promotional_worker_payment !== null)
                                <div class="font-semibold text-amber-700">Rs. {{ number_format($job->promotional_worker_payment, 2) }}</div>
                                <div class="text-[10px] text-slate-400 line-through">Rs. {{ number_format($job->default_worker_payment, 2) }}</div>
                            @else
                                <div class="font-semibold text-blue-600">Rs. {{ number_format($job->default_worker_payment, 2) }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($job->is_promotional && $job->promotional_commission !== null)
                                <div class="font-black text-emerald-700">Rs. {{ number_format($job->promotional_commission, 2) }}</div>
                                <div class="text-[10px] text-slate-400 line-through">Rs. {{ number_format($job->default_commission, 2) }}</div>
                            @else
                                <div class="font-black text-emerald-600">Rs. {{ number_format($job->default_commission, 2) }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $job->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                {{ $job->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('jobs.edit', $job) }}" class="p-1.5 text-slate-600 hover:text-blue-600 rounded-md hover:bg-slate-100 transition inline-block">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-6 text-center text-slate-400">No services cataloged yet.</td></tr>
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

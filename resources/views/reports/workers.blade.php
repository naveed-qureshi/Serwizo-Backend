@extends('layouts.app')

@section('title', 'Worker Productivity Report')
@section('header_title', 'Worker Productivity & Status Report')
@section('header_subtitle', 'City-wise and Category-wise workforce performance metrics')

@section('content')
<div class="space-y-6 pt-4 text-xs">

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('reports.workers') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <select name="city_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>

            <select name="category_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                @endforeach
            </select>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold">Filter Workers</button>
                <button type="button" onclick="window.print()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold"><i class="fa-solid fa-print"></i></button>
            </div>
        </form>
    </div>

    <!-- Workers Performance Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Worker Code & Name</th>
                        <th class="py-3 px-4">City</th>
                        <th class="py-3 px-4">Status & Availability</th>
                        <th class="py-3 px-4 text-center">Lifetime Dispatches</th>
                        <th class="py-3 px-4 text-center">Completed Jobs</th>
                        <th class="py-3 px-4 text-right">Current Ledger Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workers as $w)
                    @php $bal = $w->getLedgerBalance(); @endphp
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3 px-4">
                            <a href="{{ route('workers.show', $w) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                {{ $w->name }}
                            </a>
                            <span class="text-[11px] text-slate-400 font-mono block">{{ $w->worker_code }} &bull; {{ $w->phone }}</span>
                        </td>
                        <td class="py-3 px-4 font-medium">{{ $w->primaryCity?->name }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $w->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $w->status }}
                            </span>
                            <span class="text-[11px] text-slate-500 block capitalize">{{ str_replace('_', ' ', $w->availability) }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-bold">{{ $w->total_jobs }}</td>
                        <td class="py-3 px-4 text-center font-bold text-emerald-600">{{ $w->completed_jobs }}</td>
                        <td class="py-3 px-4 text-right font-black text-sm {{ $bal >= 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                            Rs. {{ number_format(abs($bal), 2) }}
                            <span class="text-[10px] font-bold block uppercase {{ $bal >= 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $bal >= 0 ? 'Payable' : 'Advance' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-400">No workers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workers->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $workers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

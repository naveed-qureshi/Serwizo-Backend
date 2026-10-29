@extends('layouts.app')

@section('title', 'Worker Ledgers')
@section('header_title', 'Worker Ledger Management')
@section('header_subtitle', 'Double-Entry Financial Ledgers, Running Balances & Payout Tracking')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Financial KPI Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Outstanding Payable</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">
                Rs. {{ number_format($totalPayable, 2) }}
            </div>
            <span class="text-[11px] text-slate-400">Total wages earned and owed to workers</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Advances / Receivables</span>
            <div class="text-2xl font-black text-amber-600 mt-1">
                Rs. {{ number_format(abs($totalReceivable), 2) }}
            </div>
            <span class="text-[11px] text-slate-400">Advance payments or deductions pending</span>
        </div>

        @if(auth()->user()->isAccounts())
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Quick Action</span>
                <p class="text-xs text-slate-600 mt-1">Post a payment to any worker</p>
            </div>
            <a href="{{ route('payments.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-sm transition">
                <i class="fa-solid fa-money-bill-wave mr-1"></i> Pay Worker
            </a>
        </div>
        @endif
    </div>

    <!-- Filter and Search Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('ledger.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search worker name, code, phone..."
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
    </div>

    <!-- Workers Ledger Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                    <tr>
                        <th class="py-3.5 px-4">Worker Code & Name</th>
                        <th class="py-3.5 px-4">Primary City</th>
                        <th class="py-3.5 px-4">Bank Title / Status</th>
                        <th class="py-3.5 px-4 text-right">Current Ledger Balance</th>
                        <th class="py-3.5 px-4 text-center">Balance Position</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workers as $worker)
                    @php $balance = $worker->getLedgerBalance(); @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <a href="{{ route('ledger.show', $worker) }}" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                {{ $worker->name }}
                            </a>
                            <span class="text-[11px] text-slate-500 font-mono">{{ $worker->worker_code }} &bull; {{ $worker->phone }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-700">{{ $worker->primaryCity?->name }}</td>
                        <td class="py-3.5 px-4">
                            @if($worker->bankDetail)
                            <div class="font-semibold text-slate-800">{{ $worker->bankDetail->bank_name }}</div>
                            <span class="px-1.5 py-0.2 rounded text-[10px] uppercase font-bold {{ $worker->bankDetail->verification_status === 'verified' ? 'text-emerald-600 bg-emerald-50' : 'text-amber-600 bg-amber-50' }}">
                                {{ $worker->bankDetail->verification_status }}
                            </span>
                            @else
                            <span class="text-slate-400">No Bank Saved</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-sm {{ $balance > 0 ? 'text-emerald-600' : ($balance < 0 ? 'text-amber-600' : 'text-slate-700') }}">
                            Rs. {{ number_format(abs($balance), 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($balance > 0)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                PAYABLE TO WORKER
                            </span>
                            @elseif($balance < 0)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                ADVANCE / RECEIVABLE
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                ZERO BALANCE
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('ledger.show', $worker) }}" 
                               class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-lg text-xs transition inline-flex items-center space-x-1">
                                <i class="fa-solid fa-book-journal-whills"></i>
                                <span>Ledger</span>
                            </a>
                            @if(auth()->user()->isAccounts())
                            <a href="{{ route('payments.create', ['worker_id' => $worker->id]) }}" 
                               class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg text-xs border border-emerald-200 transition inline-flex items-center space-x-1">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>Pay</span>
                            </a>
                            @endif
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

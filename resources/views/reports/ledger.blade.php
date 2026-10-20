@extends('layouts.app')

@section('title', 'Ledger Report')
@section('header_title', 'Worker Ledger Transaction Audit Report')
@section('header_subtitle', 'System-wide transaction volume, total earnings and cash disbursements')

@section('content')
<div class="space-y-6 pt-4 text-xs">

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h3 class="font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
            <i class="fa-solid fa-book-journal-whills text-emerald-600"></i>
            <span>Financial Audit Window</span>
        </h3>

        <form method="GET" action="{{ route('reports.ledger') }}" class="flex items-center space-x-2">
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            <span class="text-slate-400">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold">Filter</button>
            <button type="button" onclick="window.print()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold"><i class="fa-solid fa-print"></i></button>
        </form>
    </div>

    <!-- Totals Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Period Total Job Earnings</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">Rs. {{ number_format($totalEarnings, 2) }}</div>
            <span class="text-[11px] text-slate-400">Wages credited to worker accounts</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Period Total Disbursed Payouts</span>
            <div class="text-2xl font-black text-blue-600 mt-1">Rs. {{ number_format($totalPayments, 2) }}</div>
            <span class="text-[11px] text-slate-400">Cash and bank transfers paid out</span>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Tx Number</th>
                        <th class="py-3 px-4">Technician</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-right">Debit (Paid)</th>
                        <th class="py-3 px-4 text-right">Credit (Earned)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-2.5 px-4 font-sans text-slate-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-2.5 px-4 font-bold text-slate-900">{{ $tx->transaction_number }}</td>
                        <td class="py-2.5 px-4 font-sans font-medium text-slate-800">{{ $tx->worker?->name }} ({{ $tx->worker?->primaryCity?->name }})</td>
                        <td class="py-2.5 px-4 font-sans">
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-semibold bg-slate-100">
                                {{ str_replace('_', ' ', $tx->transaction_type) }}
                            </span>
                        </td>
                        <td class="py-2.5 px-4 font-sans text-slate-600">{{ $tx->description }}</td>
                        <td class="py-2.5 px-4 text-right font-bold text-rose-600">{{ $tx->debit > 0 ? 'Rs. ' . number_format($tx->debit, 2) : '-' }}</td>
                        <td class="py-2.5 px-4 text-right font-bold text-emerald-600">{{ $tx->credit > 0 ? 'Rs. ' . number_format($tx->credit, 2) : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-400 font-sans">No transactions found in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

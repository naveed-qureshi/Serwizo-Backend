@extends('layouts.app')

@section('title', 'Worker Payments')
@section('header_title', 'Worker Disbursements & Payouts')
@section('header_subtitle', 'Recorded Bank Transfers, Cash & Mobile Wallet Disbursals')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Action & Filter Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('payments.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
            <select name="worker_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700">
                <option value="">All Workers</option>
                @foreach($workers as $w)
                <option value="{{ $w->id }}" {{ request('worker_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->worker_code }})</option>
                @endforeach
            </select>

            <select name="payment_method" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700">
                <option value="">All Payment Methods</option>
                <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="jazzcash" {{ request('payment_method') === 'jazzcash' ? 'selected' : '' }}>JazzCash</option>
                <option value="easypaisa" {{ request('payment_method') === 'easypaisa' ? 'selected' : '' }}>EasyPaisa</option>
                <option value="other" {{ request('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
            </select>

            <div class="flex items-center space-x-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-2 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-[11px]">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold shrink-0">Filter</button>
            </div>
        </form>

        <a href="{{ route('payments.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> Pay Worker
        </a>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                    <tr>
                        <th class="py-3.5 px-4">Payment Voucher #</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Worker</th>
                        <th class="py-3.5 px-4">Method & Reference</th>
                        <th class="py-3.5 px-4 text-right">Disbursed Amount</th>
                        <th class="py-3.5 px-4">Posted By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $p->payment_number }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $p->payment_date->format('M d, Y') }}</td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('workers.show', $p->worker) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                {{ $p->worker->name }}
                            </a>
                            <span class="text-[11px] text-slate-400 font-mono block">{{ $p->worker->worker_code }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-semibold text-slate-800 uppercase text-[10px] px-2 py-0.5 rounded bg-slate-100">
                                {{ str_replace('_', ' ', $p->payment_method) }}
                            </span>
                            @if($p->reference_number)
                            <span class="text-[11px] text-slate-500 font-mono block mt-0.5">Ref: {{ $p->reference_number }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-blue-600 text-sm">
                            Rs. {{ number_format($p->amount, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $p->createdBy?->name ?? 'Accounts Officer' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400">No payment records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

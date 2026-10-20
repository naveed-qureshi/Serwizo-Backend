@extends('layouts.app')

@section('title', 'Ledger - ' . $worker->name)
@section('header_title', 'Worker Ledger: ' . $worker->name)
@section('header_subtitle', 'Worker Code: ' . $worker->worker_code . ' &bull; Phone: ' . $worker->phone)

@section('content')
<div class="space-y-6 pt-4" x-data="{ adjustModal: false, reverseModal: false, selectedTx: null, txNumber: '' }">

    <!-- Worker Ledger Summary Header -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1">
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-bold text-slate-900">{{ $worker->name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $currentBalance >= 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                    {{ $currentBalance >= 0 ? 'PAYABLE TO WORKER' : 'ADVANCE / RECEIVABLE' }}
                </span>
            </div>
            <div class="text-xs text-slate-500">
                <span>Code: <strong class="font-mono text-slate-700">{{ $worker->worker_code }}</strong></span> &bull; 
                <span>City: <strong class="text-slate-700">{{ $worker->primaryCity?->name }}</strong></span> &bull;
                <span>Bank: <strong class="text-slate-700">{{ $worker->bankDetail?->bank_name ?? 'N/A' }} ({{ $worker->bankDetail?->account_number ?? 'No A/C' }})</strong></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button @click="adjustModal = true" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-lg text-xs transition">
                <i class="fa-solid fa-plus-minus mr-1"></i> Post Adjustment
            </button>
            <a href="{{ route('payments.create', ['worker_id' => $worker->id]) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs shadow-sm transition">
                <i class="fa-solid fa-money-bill-wave mr-1"></i> Pay Worker
            </a>
            <a href="{{ route('ledger.export', ['worker' => $worker->id, 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'type' => request('type')]) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs shadow-sm transition flex items-center gap-1.5" title="Download Excel Format">
                <i class="fa-solid fa-file-excel"></i>
                <span>Download Excel</span>
            </a>
            <button onclick="window.print()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">
                <i class="fa-solid fa-print mr-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Lifetime Earnings</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">Rs. {{ number_format($totalEarnings, 2) }}</div>
            <span class="text-[11px] text-slate-400">From completed verified services</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Payments Disbursed</span>
            <div class="text-2xl font-black text-blue-600 mt-1">Rs. {{ number_format($totalPayments, 2) }}</div>
            <span class="text-[11px] text-slate-400">Via Bank Transfer, Cash & Mobile Wallets</span>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Current Running Balance</span>
            <div class="text-2xl font-black mt-1 {{ $currentBalance >= 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                Rs. {{ number_format(abs($currentBalance), 2) }}
            </div>
            <span class="text-[11px] font-semibold {{ $currentBalance >= 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ $currentBalance >= 0 ? 'Net Payable to Worker' : 'Receivable / Advance from Worker' }}
            </span>
        </div>
    </div>

    <!-- Transaction Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-list-ol text-emerald-600"></i>
                <span>Ledger Transaction Statement</span>
            </h3>

            <!-- Date Filter -->
            <form method="GET" action="{{ route('ledger.show', $worker) }}" class="flex items-center space-x-2 text-xs">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-2 py-1.5 bg-white border border-slate-300 rounded-lg">
                <span class="text-slate-400">to</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-2 py-1.5 bg-white border border-slate-300 rounded-lg">
                <select name="type" class="px-2 py-1.5 bg-white border border-slate-300 rounded-lg">
                    <option value="">All Types</option>
                    <option value="job_earning" {{ request('type') === 'job_earning' ? 'selected' : '' }}>Job Earning</option>
                    <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>Payment</option>
                    <option value="advance" {{ request('type') === 'advance' ? 'selected' : '' }}>Advance</option>
                    <option value="bonus" {{ request('type') === 'bonus' ? 'selected' : '' }}>Bonus</option>
                    <option value="deduction" {{ request('type') === 'deduction' ? 'selected' : '' }}>Deduction</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 whitespace-nowrap">Date & Time</th>
                        <th class="py-3 px-4 whitespace-nowrap">SR Number</th>
                        <th class="py-3 px-4 min-w-[220px]">Description (Customer & Job Title)</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap">Debit</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap">Credit</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap bg-slate-200/50">Balance</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                    @php
                        $sr = $tx->serviceRequest;
                    @endphp
                    <tr class="{{ $tx->is_reversed ? 'opacity-40 bg-rose-50/30 line-through' : 'hover:bg-slate-50/80 transition' }}">
                        <!-- 1. Date & Time -->
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap text-[11px] font-sans">
                            <div class="font-medium text-slate-800">{{ $tx->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('h:i A') }}</div>
                        </td>

                        <!-- 2. SR Number -->
                        <td class="py-3 px-4 whitespace-nowrap font-mono text-xs">
                            @if($sr)
                            <a href="{{ route('service-requests.show', $sr) }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                {{ $sr->sr_number }}
                            </a>
                            @else
                            <span class="text-slate-400 font-mono">{{ $tx->reference_number ?: '-' }}</span>
                            @endif
                        </td>

                        <!-- 3. Description (Customer name, and job title) -->
                        <td class="py-3 px-4 font-sans text-xs">
                            @if($sr)
                            <div class="font-semibold text-slate-800">
                                {{ $sr->customer?->name ?? 'Customer' }}
                            </div>
                            <div class="text-slate-600 text-[11px] mt-0.5">
                                {{ $sr->serviceJob?->title ?? $sr->customer_complaint ?? $sr->category?->name ?? 'Service Job' }}
                                @if($sr->model_number)
                                <span class="text-slate-400 font-normal">&bull; Model: <span class="font-mono text-slate-600">{{ $sr->model_number }}</span></span>
                                @endif
                            </div>
                            @if($tx->description && $tx->description !== 'Job completed earning')
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $tx->description }}</div>
                            @endif
                            @else
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ in_array($tx->transaction_type, ['job_earning', 'bonus']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : ($tx->transaction_type === 'payment' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                    {{ str_replace('_', ' ', $tx->transaction_type) }}
                                </span>
                            </div>
                            <div class="text-slate-700 font-medium text-[11px]">{{ $tx->description }}</div>
                            @endif
                        </td>

                        <!-- 4. Debit -->
                        <td class="py-3 px-4 text-right font-bold text-rose-600 font-mono text-xs whitespace-nowrap">
                            {{ $tx->debit > 0 ? 'Rs. ' . number_format($tx->debit, 2) : '-' }}
                        </td>

                        <!-- 5. Credit -->
                        <td class="py-3 px-4 text-right font-bold text-emerald-600 font-mono text-xs whitespace-nowrap">
                            {{ $tx->credit > 0 ? 'Rs. ' . number_format($tx->credit, 2) : '-' }}
                        </td>

                        <!-- 6. Balance -->
                        <td class="py-3 px-4 text-right font-black text-slate-900 font-mono text-sm whitespace-nowrap bg-slate-50/50">
                            Rs. {{ number_format($tx->running_balance, 2) }}
                        </td>

                        <!-- Action -->
                        <td class="py-3 px-3 text-center font-sans whitespace-nowrap">
                            @if(!$tx->is_reversed && $tx->transaction_type !== 'reversal')
                            <button @click="selectedTx = {{ $tx->id }}; txNumber = '{{ $tx->transaction_number }}'; reverseModal = true" 
                                    class="px-2 py-1 bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 rounded text-[11px] font-semibold transition" title="Reverse Transaction">
                                <i class="fa-solid fa-rotate-left mr-0.5"></i> Void
                            </button>
                            @elseif($tx->is_reversed)
                            <span class="text-[10px] text-rose-500 font-bold uppercase font-sans">Reversed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400 font-sans">No transactions recorded for this period.</td></tr>
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

    <!-- Post Manual Adjustment Modal -->
    <div x-show="adjustModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 text-xs border border-slate-200">
            <h3 class="text-base font-bold text-slate-900">Post Manual Ledger Entry</h3>
            <p class="text-slate-500">Record a bonus, penalty deduction, manual adjustment, or advance.</p>

            <form action="{{ route('ledger.transaction', $worker) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Transaction Type <span class="text-rose-500">*</span></label>
                    <select name="transaction_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="bonus">Bonus (+ Credit to Worker)</option>
                        <option value="deduction">Deduction / Penalty (- Debit from Worker)</option>
                        <option value="adjustment">Balance Adjustment</option>
                        <option value="advance">Cash Advance (- Debit from Worker)</option>
                        <option value="refund">Refund / Re-imbursement (+ Credit)</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Amount (Rs.) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" min="0.01" step="0.01" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="500">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Reason / Description <span class="text-rose-500">*</span></label>
                    <textarea name="description" rows="2" required
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                              placeholder="e.g. Excellent service rating bonus, Tool deduction..."></textarea>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Reference Number (Optional)</label>
                    <input type="text" name="reference_number"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Ref / Voucher #">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="adjustModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm">Post to Ledger</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reverse / Void Transaction Modal -->
    <div x-show="reverseModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 text-xs border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 text-rose-600">Reverse Transaction: <span x-text="txNumber"></span></h3>
            <p class="text-slate-500">This will safely create an equal opposing reversal entry without deleting financial history.</p>

            <form :action="'/ledger/transaction/' + selectedTx + '/reverse'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Reason for Reversal <span class="text-rose-500">*</span></label>
                    <textarea name="reversal_reason" rows="3" required
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 outline-none"
                              placeholder="Why is this transaction being reversed?"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="reverseModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg shadow-sm">Confirm Reversal</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

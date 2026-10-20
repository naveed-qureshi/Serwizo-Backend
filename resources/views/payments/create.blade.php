@extends('layouts.app')

@section('title', 'Pay Worker')
@section('header_title', 'Record Worker Payment')
@section('header_subtitle', 'Post cash, bank transfer or wallet payout with automatic ledger debit')

@section('content')
<div class="max-w-2xl mx-auto py-6" x-data="paymentForm()">
    @if ($errors->any())
    <div class="mb-4 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>Unable to process payment:</span>
        </div>
        <ul class="list-disc list-inside ml-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('payments.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
        @csrf
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">Disbursement Details</h3>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Select Technician / Worker <span class="text-rose-500">*</span></label>
            <select name="worker_id" required x-model="selectedWorkerId" @change="onWorkerChange()"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none font-medium">
                <option value="">Select Worker</option>
                @foreach($workers as $w)
                <option value="{{ $w->id }}" {{ (old('worker_id', $selectedWorkerId) == $w->id) ? 'selected' : '' }}>
                    {{ $w->name }} ({{ $w->worker_code }}) - Bal: Rs. {{ number_format($w->ledger_balance ?? $w->getLedgerBalance(), 2) }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Worker Balance Summary Card -->
        <template x-if="currentWorker">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between">
                <div>
                    <span class="text-slate-500 block text-[11px]">Worker Total Balance</span>
                    <span class="text-base font-bold" :class="currentWorker.balance > 0 ? 'text-emerald-700' : 'text-slate-700'">
                        Rs. <span x-text="formatNumber(currentWorker.balance)"></span>
                    </span>
                    <span class="text-[11px] text-slate-400 ml-1 font-normal" x-show="currentWorker.balance > 0">(Receivable by worker)</span>
                </div>
                <template x-if="currentWorker.balance > 0">
                    <button type="button" @click="fillFullBalance()" class="px-2.5 py-1 text-[11px] font-semibold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded transition">
                        <i class="fa-solid fa-coins mr-1"></i> Pay Full Balance
                    </button>
                </template>
            </div>
        </template>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="font-semibold text-slate-700 block">Disbursement Amount (Rs.) <span class="text-rose-500">*</span></label>
                <span class="text-[11px] text-slate-400" x-show="currentWorker && currentWorker.balance > 0">
                    Max: Rs. <span x-text="formatNumber(currentWorker.balance)"></span>
                </span>
            </div>
            <input type="number" name="amount" x-model="amount" value="{{ old('amount') }}" min="0.01" step="0.01" :max="currentWorker && currentWorker.balance > 0 ? currentWorker.balance : null" required
                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none"
                   placeholder="Enter any amount (e.g. 2000)">

            <!-- Warning if entered amount exceeds balance -->
            <template x-if="currentWorker && amount && parseFloat(amount) > currentWorker.balance">
                <p class="text-rose-600 font-semibold text-[11px] mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Amount cannot exceed worker's total balance (Rs. <span x-text="formatNumber(currentWorker.balance)"></span>).</span>
                </p>
            </template>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="font-semibold text-slate-700 block mb-1">Payment Date <span class="text-rose-500">*</span></label>
                <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="font-semibold text-slate-700 block mb-1">Payment Method <span class="text-rose-500">*</span></label>
                <select name="payment_method" required x-model="paymentMethod"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="bank_transfer">Bank Transfer (1LINK / IBFT)</option>
                    <option value="cash">Direct Cash Handover</option>
                    <option value="jazzcash">JazzCash Mobile Wallet</option>
                    <option value="easypaisa">EasyPaisa Mobile Wallet</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="font-semibold text-slate-700 block mb-1">Transaction Reference / Voucher #</label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                       placeholder="e.g. IBFT-987654321">
            </div>

            <div>
                <label class="font-semibold text-slate-700 block mb-1">Disbursing Bank / Source</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                       placeholder="e.g. SerWizo HBL Corporate A/C">
            </div>
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Internal Accounts Notes / Remarks</label>
            <textarea name="notes" rows="2"
                      class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                      placeholder="Remarks on payout...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
            <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition"
                    :disabled="currentWorker && amount && parseFloat(amount) > currentWorker.balance">
                <i class="fa-solid fa-check mr-1"></i> Post Payment to Ledger
            </button>
        </div>
    </form>
</div>

<script>
    function paymentForm() {
        const workersMap = @json($workersMap);
        const initWorkerId = '{{ old('worker_id', $selectedWorkerId ?? '') }}';

        return {
            selectedWorkerId: initWorkerId,
            currentWorker: workersMap[initWorkerId] || null,
            amount: '{{ old('amount') }}',
            paymentMethod: '{{ old('payment_method', 'bank_transfer') }}',

            onWorkerChange() {
                this.currentWorker = workersMap[this.selectedWorkerId] || null;
            },

            fillFullBalance() {
                if (this.currentWorker && this.currentWorker.balance > 0) {
                    this.amount = this.currentWorker.balance;
                }
            },

            formatNumber(val) {
                return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    }
</script>
@endsection


@extends('layouts.app')

@section('title', 'Brand Ledgers & Receivables')
@section('header_title', 'Brand Ledgers')
@section('header_subtitle', 'Corporate Receivable Statements, Payment Receipts & Audit Trail')

@section('content')
<div class="space-y-6 pt-4" x-data="{ paymentModal: false, adjustmentModal: false, reverseModal: false, selectedTx: null }">

    <!-- Brand Selector & Filter Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('brand-ledger.index') }}" class="flex flex-1 flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-64">
                <select name="brand_id" onchange="this.form.submit()" class="w-full py-1.5 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white font-bold text-slate-800">
                    <option value="">-- Select Brand --</option>
                    @foreach($brands as $b)
                    <option value="{{ $b->id }}" {{ ($selectedBrand && $selectedBrand->id == $b->id) ? 'selected' : '' }}>
                        {{ $b->name }} ({{ $b->code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2 text-xs w-full sm:w-auto">
                <input type="date" name="start_date" value="{{ request('start_date', Carbon\Carbon::now()->startOfMonth()->toDateString()) }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
                <span class="text-slate-400">to</span>
                <input type="date" name="end_date" value="{{ request('end_date', Carbon\Carbon::now()->endOfMonth()->toDateString()) }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
                <button type="submit" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold transition">Filter</button>
            </div>
        </form>

        @if($selectedBrand && auth()->user()->isAccounts())
        <div class="flex items-center space-x-2 shrink-0">
            <button @click="paymentModal = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-money-bill-transfer mr-1.5"></i>
                <span>Record Payment</span>
            </button>
            <button @click="adjustmentModal = true" class="px-3.5 py-2 bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-sliders mr-1.5"></i>
                <span>Adjustment</span>
            </button>
        </div>
        @endif
    </div>

    @if($selectedBrand)
    <!-- Summary Financial Card -->
    <div class="bg-gradient-to-r from-slate-900 via-navy-900 to-slate-950 p-6 rounded-2xl shadow-lg text-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-2 border-b border-slate-800">
            <div>
                <h3 class="text-base font-bold uppercase tracking-wider text-emerald-400">{{ $selectedBrand->name }}</h3>
                <span class="text-xs text-slate-400">Corporate Receivable Ledger &bull; Code: {{ $selectedBrand->code }}</span>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block uppercase">Current Net Balance Receivable</span>
                <span class="text-2xl font-black {{ $currentBalance > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                    Rs. {{ number_format($currentBalance, 2) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div class="p-3 rounded-lg bg-white/5">
                <span class="text-slate-400 block">Opening Balance</span>
                <span class="text-base font-bold text-white mt-1 block">Rs. {{ number_format($openingBalance, 2) }}</span>
            </div>
            <div class="p-3 rounded-lg bg-white/5">
                <span class="text-slate-400 block">Total Billings (Debit)</span>
                <span class="text-base font-bold text-amber-400 mt-1 block">Rs. {{ number_format($totalDebit, 2) }}</span>
            </div>
            <div class="p-3 rounded-lg bg-white/5">
                <span class="text-slate-400 block">Payments Received (Credit)</span>
                <span class="text-base font-bold text-emerald-400 mt-1 block">Rs. {{ number_format($totalCredit, 2) }}</span>
            </div>
            <div class="p-3 rounded-lg bg-white/5">
                <span class="text-slate-400 block">Closing Balance</span>
                <span class="text-base font-bold text-white mt-1 block">Rs. {{ number_format($openingBalance + $totalDebit - $totalCredit, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Ledger Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                <span>Statement of Account &bull; {{ $selectedBrand->name }} ({{ $transactions->count() }} Records)</span>
            </h4>
            <div class="flex items-center space-x-2">
                <a href="{{ route('brand-ledger.export', ['brand' => $selectedBrand->id, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5" title="Download Excel Format">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Download Excel</span>
                </a>
                <button onclick="window.print()" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-semibold rounded-lg shadow-sm transition">
                    <i class="fa-solid fa-print mr-1"></i> Print Statement
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 uppercase tracking-wider font-bold text-[11px] border-b border-slate-200">
                        <th class="py-3 px-3 whitespace-nowrap">Assigned At</th>
                        <th class="py-3 px-3 whitespace-nowrap">Completed At</th>
                        <th class="py-3 px-2 text-center whitespace-nowrap">Duration</th>
                        <th class="py-3 px-3 whitespace-nowrap">Job number</th>
                        <th class="py-3 px-3 whitespace-nowrap">Customer</th>
                        <th class="py-3 px-3 whitespace-nowrap">Contact</th>
                        <th class="py-3 px-3 min-w-[200px]">Description</th>
                        <th class="py-3 px-2 text-center whitespace-nowrap">City</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap">Debit</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap">Credit</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap bg-slate-200/50">Balance</th>
                        <th class="py-3 px-2 text-center whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Opening Balance Row -->
                    <tr class="bg-slate-50/80 font-bold text-slate-700 border-b border-slate-200">
                        <td class="py-2.5 px-3 text-slate-500 font-mono text-[11px]" colspan="3">
                            <span class="inline-flex items-center gap-1.5 uppercase tracking-wider text-[10px] bg-slate-200 px-2 py-0.5 rounded text-slate-700 font-bold">
                                <i class="fa-solid fa-clock-rotate-left"></i> Balance B/F
                            </span>
                        </td>
                        <td class="py-2.5 px-3 uppercase tracking-wider font-bold text-slate-800" colspan="5">
                            OPENING BALANCE BROUGHT FORWARD
                        </td>
                        <td class="py-2.5 px-3 text-right text-slate-400 font-mono">-</td>
                        <td class="py-2.5 px-3 text-right text-slate-400 font-mono">-</td>
                        <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900 bg-slate-100">
                            Rs. {{ number_format($openingBalance, 2) }}
                        </td>
                        <td class="py-2.5 px-2 text-center text-slate-400">-</td>
                    </tr>

                    @forelse($transactions as $tx)
                    @php
                        $sr = $tx->serviceRequest;
                        $hasJob = (bool)$sr;
                        $assignedTime = $hasJob ? ($sr->assigned_at ?? $sr->started_at ?? $sr->created_at) : $tx->created_at;
                        $completionTime = $hasJob ? ($sr->completed_at ?? $sr->verified_at) : null;
                        $durationStr = $hasJob ? $sr->formatted_duration : '-';
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ $tx->is_reversed ? 'opacity-40 line-through bg-rose-50/30' : '' }}">
                        <!-- 1. Assigned At -->
                        <td class="py-3 px-3 text-slate-600 whitespace-nowrap text-[11px]">
                            <div class="font-medium">{{ $assignedTime ? $assignedTime->format('d M Y') : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $assignedTime ? $assignedTime->format('h:i A') : '' }}</div>
                        </td>

                        <!-- 2. Completed At -->
                        <td class="py-3 px-3 text-slate-600 whitespace-nowrap text-[11px]">
                            @if($completionTime)
                            <div class="font-medium text-slate-700">{{ $completionTime->format('d M Y') }}</div>
                            <div class="text-[10px] text-emerald-600 font-semibold">{{ $completionTime->format('h:i A') }}</div>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- 3. Duration -->
                        <td class="py-3 px-2 text-center whitespace-nowrap">
                            @if($durationStr !== '-')
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-mono font-semibold text-[10px] border border-blue-100">
                                <i class="fa-regular fa-clock mr-0.5"></i> {{ $durationStr }}
                            </span>
                            @else
                            <span class="text-slate-400 font-mono text-[11px]">-</span>
                            @endif
                        </td>

                        <!-- 4. Job number -->
                        <td class="py-3 px-3 whitespace-nowrap font-mono text-xs">
                            @if($hasJob)
                            <a href="{{ route('service-requests.show', $sr) }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                {{ $sr->sr_number }}
                            </a>
                            @else
                            <span class="font-bold text-slate-700">{{ $tx->reference_number ?: $tx->transaction_number }}</span>
                            @endif
                        </td>

                        <!-- 5. Customer -->
                        <td class="py-3 px-3 whitespace-nowrap">
                            @if($hasJob && $sr->customer)
                            <span class="font-semibold text-slate-800 text-xs">{{ $sr->customer->name }}</span>
                            @elseif(!$hasJob)
                            <span class="text-slate-500 italic text-[11px]">Corporate Settlement</span>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- 6. Contact -->
                        <td class="py-3 px-3 whitespace-nowrap font-mono text-[11px] text-slate-600">
                            @if($hasJob && $sr->customer?->phone)
                            <a href="tel:{{ $sr->customer->phone }}" class="hover:text-emerald-600">
                                {{ $sr->customer->phone }}
                            </a>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- 7. Description -->
                        <td class="py-3 px-3 text-slate-700 text-[11px] leading-relaxed">
                            @if($hasJob)
                            <div class="font-medium text-slate-800 text-[11px]">{{ $sr->serviceJob?->title ?? 'Installation Service' }}</div>
                            @if($sr->model_number || $sr->serial_number)
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-slate-500 mt-0.5">
                                @if($sr->model_number)
                                <span>Model: <span class="text-slate-700 font-mono">{{ $sr->model_number }}</span></span>
                                @endif
                                @if($sr->serial_number)
                                @if($sr->model_number)<span class="text-slate-300">&bull;</span>@endif
                                <span>S/N: <span class="text-slate-700 font-mono">{{ $sr->serial_number }}</span></span>
                                @endif
                            </div>
                            @endif
                            @else
                            <div class="text-slate-700 text-[11px]">{{ $tx->description }}</div>
                            @endif
                        </td>

                        <!-- 8. City -->
                        <td class="py-3 px-2 text-center whitespace-nowrap">
                            @php
                                $cityName = $hasJob ? ($sr->city?->name ?? $sr->customer?->city?->name) : null;
                            @endphp
                            @if($cityName)
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold text-[10px] border border-slate-200">
                                {{ $cityName }}
                            </span>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- 9. Debit -->
                        <td class="py-3 px-3 text-right font-mono text-xs whitespace-nowrap {{ $tx->debit > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' }}">
                            {{ $tx->debit > 0 ? 'Rs. ' . number_format($tx->debit, 2) : '-' }}
                        </td>

                        <!-- 10. Credit -->
                        <td class="py-3 px-3 text-right font-mono text-xs whitespace-nowrap {{ $tx->credit > 0 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                            {{ $tx->credit > 0 ? 'Rs. ' . number_format($tx->credit, 2) : '-' }}
                        </td>

                        <!-- 11. Balance -->
                        <td class="py-3 px-3 text-right font-mono font-black text-xs text-slate-900 whitespace-nowrap bg-slate-50">
                            Rs. {{ number_format($tx->statement_balance ?? $tx->running_balance, 2) }}
                        </td>

                        <!-- Action: Reverse -->
                        <td class="py-3 px-2 text-center whitespace-nowrap">
                            @if(!$tx->is_reversed && $tx->transaction_type !== 'reversal')
                                @if(auth()->user()->isAccounts())
                                <button @click="selectedTx = {{ $tx }}; reverseModal = true" title="Reverse Transaction" class="text-slate-400 hover:text-rose-600 p-1 transition">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                                @else
                                <span class="text-[9px] text-slate-400 font-medium">Posted</span>
                                @endif
                            @elseif($tx->is_reversed)
                            <span class="text-[9px] text-rose-500 font-bold uppercase bg-rose-50 px-1 py-0.5 rounded">Reversed</span>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-file-circle-xmark text-3xl mb-2 text-slate-300 block"></i>
                            No ledger transactions recorded in this date range.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <!-- Closing Totals Footer -->
                @if($transactions->count() > 0)
                <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-xs">
                    <tr>
                        <td colspan="8" class="py-3 px-3 uppercase tracking-wider text-slate-700">
                            Period Totals & Closing Balance
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-amber-800">
                            Rs. {{ number_format($totalDebit, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-800">
                            Rs. {{ number_format($totalCredit, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-black text-slate-900 bg-slate-200">
                            Rs. {{ number_format($openingBalance + $totalDebit - $totalCredit, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if(auth()->user()->isAccounts())
    <!-- Record Payment Modal -->
    <div x-show="paymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60" x-cloak>
        <div @click.away="paymentModal = false" class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-900">Record Brand Payment Receipt</h3>
                <button @click="paymentModal = false" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST" action="{{ route('brand-ledger.payment', $selectedBrand) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Amount Received (PKR) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="amount" required placeholder="e.g. 50000" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm font-bold text-slate-900">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Payment Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Payment Method <span class="text-rose-500">*</span></label>
                        <select name="payment_method" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="online">Online / IBFT</option>
                            <option value="cash">Cash</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Reference / Cheque No</label>
                        <input type="text" name="reference_number" placeholder="e.g. FT-987654" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" placeholder="e.g. Meezan Bank" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Payment remarks..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="paymentModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm">Save Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Adjustment Modal -->
    <div x-show="adjustmentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60" x-cloak>
        <div @click.away="adjustmentModal = false" class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-900">Post Manual Brand Adjustment</h3>
                <button @click="adjustmentModal = false" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST" action="{{ route('brand-ledger.adjustment', $selectedBrand) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Adjustment Type <span class="text-rose-500">*</span></label>
                    <select name="transaction_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                        <option value="debit_adjustment">Debit Adjustment (Increase Receivable / Extra Charge)</option>
                        <option value="credit_adjustment">Credit Adjustment (Decrease Receivable / Credit Note / Discount)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Amount (PKR) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="amount" required placeholder="e.g. 500" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Description / Reason <span class="text-rose-500">*</span></label>
                    <input type="text" name="description" required placeholder="e.g. Special claim settlement" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="adjustmentModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg shadow-sm">Post Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reverse Transaction Modal -->
    <div x-show="reverseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60" x-cloak>
        <div @click.away="reverseModal = false" class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-rose-600">Reverse Transaction</h3>
                <button @click="reverseModal = false" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form x-bind:action="'/brand-ledger/' + (selectedTx ? selectedTx.id : '') + '/reverse'" method="POST" class="space-y-4">
                @csrf
                <p class="text-slate-600">
                    Are you sure you want to reverse transaction <strong class="font-mono text-slate-900" x-text="selectedTx ? selectedTx.transaction_number : ''"></strong>? An offsetting double-entry reversal transaction will be created for full audit compliance.
                </p>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Reason for Reversal <span class="text-rose-500">*</span></label>
                    <textarea name="reason" required rows="2" placeholder="e.g. Entry posted in error, cancelled job..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="reverseModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg shadow-sm">Confirm Reversal</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @else
    <div class="bg-white p-12 rounded-xl text-center shadow-sm border border-slate-200">
        <i class="fa-solid fa-building text-slate-300 text-4xl mb-3"></i>
        <h3 class="font-bold text-slate-700 text-base">Select a Brand</h3>
        <p class="text-slate-400 text-xs mt-1">Please select a brand from the dropdown above to view their financial statement and receivable records.</p>
    </div>
    @endif

</div>
@endsection

@extends('layouts.app')

@section('title', 'Ledger Statement - ' . $brand->name)
@section('header_title', $brand->name . ' Account Statement')
@section('header_subtitle', 'Installation Billings, Settlement Payments & Running Balance')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Financial Card -->
    <div class="bg-gradient-to-r from-navy-900 via-slate-900 to-emerald-950 p-6 rounded-2xl shadow-lg text-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-2 border-b border-slate-800">
            <div>
                <span class="text-xs text-slate-400 uppercase font-semibold">Account Statement</span>
                <h3 class="text-xl font-black text-white mt-0.5">{{ $brand->name }}</h3>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block uppercase">Current Outstanding Balance</span>
                <span class="text-2xl font-black text-amber-400">
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
                <span class="text-slate-400 block">Period Billings (Debit)</span>
                <span class="text-base font-bold text-amber-400 mt-1 block">Rs. {{ number_format($totalDebit, 2) }}</span>
            </div>
            <div class="p-3 rounded-lg bg-white/5">
                <span class="text-slate-400 block">Period Payments (Credit)</span>
                <span class="text-base font-bold text-emerald-400 mt-1 block">Rs. {{ number_format($totalCredit, 2) }}</span>
            </div>
            <div class="p-3 rounded-lg bg-white/5">
                <span class="text-slate-400 block">Closing Balance</span>
                <span class="text-base font-bold text-white mt-1 block">Rs. {{ number_format($openingBalance + $totalDebit - $totalCredit, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('brand-portal.ledger') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <span class="font-semibold text-slate-600 uppercase text-[11px]">Date Range:</span>
            <input type="date" name="start_date" value="{{ request('start_date', $startDate->toDateString()) }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            <span class="text-slate-400">to</span>
            <input type="date" name="end_date" value="{{ request('end_date', $endDate->toDateString()) }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg transition">Filter Statement</button>
        </form>
    </div>

    <!-- Statement Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                <span>Corporate Statement of Account &bull; {{ $transactions->count() }} Records</span>
            </h4>
            <div class="flex items-center space-x-2">
                <a href="{{ route('brand-portal.ledger.export', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5" title="Download Excel Format">
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
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
                    </tr>

                    @forelse($transactions as $tx)
                    @php
                        $sr = $tx->serviceRequest;
                        $hasJob = (bool)$sr;
                        $assignedTime = $hasJob ? ($sr->assigned_at ?? $sr->started_at ?? $sr->created_at) : $tx->created_at;
                        $completionTime = $hasJob ? ($sr->completed_at ?? $sr->verified_at) : null;
                        $durationStr = $hasJob ? $sr->formatted_duration : '-';
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ $tx->is_reversed ? 'opacity-40 line-through' : '' }}">
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
                            <a href="{{ route('brand-portal.jobs.show', $sr) }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-file-circle-xmark text-3xl mb-2 text-slate-300 block"></i>
                            No transactions recorded in this period.
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
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection

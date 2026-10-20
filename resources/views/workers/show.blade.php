@extends('layouts.app')

@section('title', $worker->name . ' - Worker Profile')
@section('header_title', 'Worker Profile: ' . $worker->name)
@section('header_subtitle', 'Worker Code: ' . $worker->worker_code . ' &bull; ' . $worker->primaryCity?->name)

@section('content')
<div class="space-y-6 pt-4" x-data="{ activeTab: 'overview' }">

    <!-- Worker Profile Header Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden border-2 border-emerald-500 shrink-0">
                @if($worker->photo_path)
                <img src="{{ $worker->photo_url }}" alt="{{ $worker->name }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'font-bold text-xl text-slate-600\'>{{ strtoupper(substr($worker->name, 0, 1)) }}</span>';">
                @else
                <i class="fa-solid fa-user text-2xl text-slate-400"></i>
                @endif
            </div>
            <div>
                <div class="flex items-center space-x-2.5">
                    <h2 class="text-xl font-bold text-slate-900">{{ $worker->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $worker->status === 'active' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200' }}">
                        {{ $worker->status }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 capitalize">
                        <i class="fa-solid fa-circle text-[8px] mr-1 {{ $worker->availability === 'available' ? 'text-emerald-500' : 'text-amber-500' }}"></i>
                        {{ str_replace('_', ' ', $worker->availability) }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-1.5 font-medium">
                    <span><i class="fa-solid fa-id-badge mr-1 text-slate-400"></i> {{ $worker->worker_code }}</span>
                    <span><i class="fa-solid fa-phone mr-1 text-slate-400"></i> {{ $worker->phone }}</span>
                    <span><i class="fa-solid fa-location-dot mr-1 text-slate-400"></i> {{ $worker->primaryCity?->name }}</span>
                    <span><i class="fa-regular fa-calendar mr-1 text-slate-400"></i> Registered {{ $worker->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('workers.edit', $worker) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-lg text-xs transition">
                <i class="fa-regular fa-pen-to-square mr-1"></i> Edit Profile
            </a>

            <!-- Quick Status Change Form -->
            <form action="{{ route('workers.status', $worker) }}" method="POST" class="inline-flex">
                @csrf
                @method('PATCH')
                <select name="availability" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 cursor-pointer">
                    <option value="available" {{ $worker->availability === 'available' ? 'selected' : '' }}>Set Available</option>
                    <option value="busy" {{ $worker->availability === 'busy' ? 'selected' : '' }}>Set Busy</option>
                    <option value="on_leave" {{ $worker->availability === 'on_leave' ? 'selected' : '' }}>Set On Leave</option>
                    <option value="unavailable" {{ $worker->availability === 'unavailable' ? 'selected' : '' }}>Set Unavailable</option>
                </select>
            </form>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Dispatches</span>
            <span class="text-xl font-black text-slate-900 mt-1 block">{{ $totalJobs }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Completed Jobs</span>
            <span class="text-xl font-black text-emerald-600 mt-1 block">{{ $completedJobs }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Cancelled Jobs</span>
            <span class="text-xl font-black text-rose-600 mt-1 block">{{ $cancelledJobs }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Current Ledger Balance</span>
            <div class="flex items-baseline space-x-1.5 mt-1">
                <span class="text-xl font-black {{ $currentBalance >= 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                    Rs. {{ number_format(abs($currentBalance), 2) }}
                </span>
                <span class="text-[10px] font-bold uppercase tracking-wider {{ $currentBalance >= 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $currentBalance >= 0 ? 'Payable' : 'Advance' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Profile Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        
        <!-- Tab Navigation -->
        <div class="flex border-b border-slate-200 bg-slate-50 px-4 text-xs font-semibold overflow-x-auto">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-emerald-600 text-emerald-600 bg-white' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-4 border-b-2 transition whitespace-nowrap">
                <i class="fa-regular fa-user mr-1.5"></i> Overview & Personal
            </button>
            <button @click="activeTab = 'skills'" :class="activeTab === 'skills' ? 'border-emerald-600 text-emerald-600 bg-white' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-4 border-b-2 transition whitespace-nowrap">
                <i class="fa-solid fa-wrench mr-1.5"></i> Skills & Categories
            </button>
            <button @click="activeTab = 'documents'" :class="activeTab === 'documents' ? 'border-emerald-600 text-emerald-600 bg-white' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-4 border-b-2 transition whitespace-nowrap">
                <i class="fa-solid fa-file-shield mr-1.5"></i> KYC Documents ({{ $worker->documents->count() }})
            </button>
            <button @click="activeTab = 'jobs'" :class="activeTab === 'jobs' ? 'border-emerald-600 text-emerald-600 bg-white' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-4 border-b-2 transition whitespace-nowrap">
                <i class="fa-solid fa-list-check mr-1.5"></i> Job History ({{ $worker->serviceRequests->count() }})
            </button>
            @if(auth()->user()->isAccounts())
            <button @click="activeTab = 'ledger'" :class="activeTab === 'ledger' ? 'border-emerald-600 text-emerald-600 bg-white' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-4 border-b-2 transition whitespace-nowrap">
                <i class="fa-solid fa-book-journal-whills mr-1.5"></i> Financial Ledger
            </button>
            @endif
        </div>

        <div class="p-6 text-xs">
            
            <!-- Tab 1: Overview -->
            <div x-show="activeTab === 'overview'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-bold text-slate-900 uppercase tracking-wider mb-3 pb-1 border-b border-slate-100">Personal Data</h4>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">CNIC Number:</dt><dd class="font-mono font-semibold text-slate-800">{{ $worker->cnic_number }}</dd></div>
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">Phone:</dt><dd class="font-semibold text-slate-800">{{ $worker->phone }}</dd></div>
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">Email:</dt><dd class="text-slate-800">{{ $worker->email ?? 'N/A' }}</dd></div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50" x-data="{ showPass: false, copied: false, openReset: false, newPass: '123456', showNewPass: false }">
                                <dt class="text-slate-500 font-medium">App PIN / Password:</dt>
                                <dd class="flex items-center space-x-1.5">
                                    @if($worker->visible_password)
                                    <span class="font-mono font-bold text-slate-800 bg-slate-50 px-2 py-0.5 rounded border border-slate-200 select-all" x-text="showPass ? '{{ addslashes($worker->visible_password) }}' : '••••••'"></span>
                                    <button type="button" @click="showPass = !showPass" class="text-slate-400 hover:text-slate-700 transition px-1" :title="showPass ? 'Hide PIN' : 'Reveal PIN'">
                                        <i :class="showPass ? 'fa-solid fa-eye-slash text-amber-600' : 'fa-solid fa-eye'"></i>
                                    </button>
                                    <button type="button" @click="navigator.clipboard.writeText('{{ addslashes($worker->visible_password) }}'); copied = true; setTimeout(() => copied = false, 2000)" class="text-slate-400 hover:text-slate-700 transition px-1" title="Copy PIN">
                                        <i :class="copied ? 'fa-solid fa-check text-emerald-600' : 'fa-regular fa-copy'"></i>
                                    </button>
                                    @else
                                    <span class="text-slate-400 italic text-[11px] bg-slate-50 px-2 py-0.5 rounded border border-slate-200">Default (123456)</span>
                                    @endif

                                    <button type="button" @click="openReset = true" class="text-[10px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-1.5 py-0.5 rounded transition">
                                        Change
                                    </button>

                                    <!-- Quick PIN Change Modal -->
                                    <div x-show="openReset" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                                        <div @click.away="openReset = false" class="bg-white rounded-2xl shadow-xl border border-slate-100 max-w-sm w-full p-5 space-y-4 animate-fade-in text-left">
                                            <div class="flex items-center justify-between border-b pb-3">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                                        <i class="fa-solid fa-key"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-slate-800 text-sm">Change Worker PIN</h4>
                                                        <p class="text-[11px] text-slate-500">{{ $worker->name }} ({{ $worker->worker_code }})</p>
                                                    </div>
                                                </div>
                                                <button type="button" @click="openReset = false" class="text-slate-400 hover:text-slate-600 p-1">
                                                    <i class="fa-solid fa-xmark text-sm"></i>
                                                </button>
                                            </div>

                                            <form action="{{ route('workers.password.update', $worker) }}" method="POST" class="space-y-3">
                                                @csrf
                                                <div>
                                                    <label class="font-semibold text-slate-700 block mb-1">New Mobile PIN / Password <span class="text-rose-500">*</span></label>
                                                    <div class="relative">
                                                        <input :type="showNewPass ? 'text' : 'password'" name="password" x-model="newPass" required minlength="4"
                                                               class="w-full px-3 py-2 pr-10 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                                        <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                                                            <i :class="showNewPass ? 'fa-solid fa-eye-slash text-amber-600' : 'fa-solid fa-eye'"></i>
                                                        </button>
                                                    </div>
                                                    <span class="text-[10px] text-slate-400 mt-1 block">Used by worker to sign in to the Worker Mobile App.</span>
                                                </div>

                                                <div class="flex space-x-2 pt-2 border-t">
                                                    <button type="button" @click="openReset = false" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold transition text-xs">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold shadow-sm transition text-xs">
                                                        Save PIN
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">Age / Date of Birth:</dt><dd class="text-slate-800">{{ $worker->age ? $worker->age . ' Years (' . $worker->date_of_birth->format('M d, Y') . ')' : 'N/A' }}</dd></div>
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">Address:</dt><dd class="text-slate-800 text-right max-w-xs">{{ $worker->address ?? 'N/A' }}</dd></div>
                        </dl>
                    </div>

                    <div>
                        <h4 class="font-bold text-slate-900 uppercase tracking-wider mb-3 pb-1 border-b border-slate-100">Bank & Payment Details</h4>
                        @if($worker->bankDetail)
                        <dl class="space-y-2.5">
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">Bank Name:</dt><dd class="font-semibold text-slate-800">{{ $worker->bankDetail->bank_name ?? 'N/A' }}</dd></div>
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">Account Title:</dt><dd class="text-slate-800">{{ $worker->bankDetail->account_title ?? 'N/A' }}</dd></div>
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">Account Number:</dt><dd class="font-mono text-slate-800">{{ $worker->bankDetail->account_number ?? 'N/A' }}</dd></div>
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">IBAN:</dt><dd class="font-mono text-slate-800">{{ $worker->bankDetail->iban ?? 'N/A' }}</dd></div>
                            <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500 font-medium">Verification:</dt>
                                <dd>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $worker->bankDetail->verification_status === 'verified' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $worker->bankDetail->verification_status }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                        @else
                        <p class="text-slate-400">No bank details recorded yet.</p>
                        @endif
                    </div>
                </div>

                <!-- References -->
                @if($worker->references->count() > 0)
                <div class="pt-4 border-t border-slate-100">
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider mb-3">Professional References</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach($worker->references as $ref)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-1">
                            <div class="font-bold text-slate-800">{{ $ref->name }}</div>
                            <div class="text-slate-600 font-mono">{{ $ref->phone }}</div>
                            <div class="text-[11px] text-slate-500">{{ $ref->relationship ?? 'Reference' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Tab 2: Skills & Service Areas -->
            <div x-show="activeTab === 'skills'" class="space-y-6">
                <div>
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider mb-3">Categories & Specializations</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($worker->workerCategories as $wCat)
                        <div class="p-3 rounded-lg border {{ $wCat->is_primary ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 border-slate-200' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-900">{{ $wCat->category?->title }}</span>
                                @if($wCat->is_primary)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-600 text-white uppercase">Primary</span>
                                @endif
                            </div>
                            <p class="text-slate-600">{{ $wCat->subCategory?->title ?? 'All Sub-Services' }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider mb-3">Service Coverage Cities</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($worker->cities as $c)
                        <span class="px-3 py-1 bg-blue-50 text-blue-800 border border-blue-200 font-semibold rounded-lg text-xs">
                            <i class="fa-solid fa-city mr-1 text-blue-500"></i> {{ $c->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tab 3: Documents KYC -->
            <div x-show="activeTab === 'documents'" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @forelse($worker->documents as $doc)
                    <div class="bg-slate-50 border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="p-3 bg-white border-b border-slate-200 flex items-center justify-between">
                            <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                            @if($doc->is_verified)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800"><i class="fa-solid fa-check-double mr-1"></i>Verified</span>
                            @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending QA</span>
                            @endif
                        </div>
                        <div class="p-4 flex flex-col items-center">
                            <div class="w-full h-44 bg-slate-200 rounded-lg flex items-center justify-center overflow-hidden border border-slate-300">
                                <img src="{{ route('workers.documents.show', $doc) }}" alt="{{ $doc->document_type }}" class="w-full h-full object-cover">
                            </div>
                            <div class="w-full mt-3 flex items-center justify-between text-[11px]">
                                <a href="{{ route('workers.documents.show', $doc) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-bold">
                                    <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Full Size
                                </a>
                                @if(!$doc->is_verified && auth()->user()->isOperations())
                                <form action="{{ route('workers.documents.verify', $doc) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 bg-emerald-600 text-white rounded font-bold hover:bg-emerald-700 transition">
                                        Verify KYC
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-3 py-8 text-center text-slate-400">No documents uploaded.</div>
                    @endforelse
                </div>
            </div>

            <!-- Tab 4: Jobs -->
            <div x-show="activeTab === 'jobs'" class="space-y-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                            <tr>
                                <th class="py-2.5 px-3">SR Number</th>
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Customer</th>
                                <th class="py-2.5 px-3">Service</th>
                                <th class="py-2.5 px-3">Wage</th>
                                <th class="py-2.5 px-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($worker->serviceRequests as $sr)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-2.5 px-3 font-bold">
                                    <a href="{{ route('service-requests.show', $sr) }}" class="text-slate-900 hover:text-emerald-600">
                                        {{ $sr->sr_number }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $sr->created_at->format('M d, Y') }}</td>
                                <td class="py-2.5 px-3">{{ $sr->customer?->name }}</td>
                                <td class="py-2.5 px-3">{{ $sr->serviceJob?->title }}</td>
                                <td class="py-2.5 px-3 font-semibold text-emerald-600">Rs. {{ number_format($sr->worker_payment, 2) }}</td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $sr->status_badge_color }}">
                                        {{ strtoupper(str_replace('_', ' ', $sr->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-6 text-center text-slate-400">No jobs assigned to this worker yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 5: Ledger -->
            @if(auth()->user()->isAccounts())
            <div x-show="activeTab === 'ledger'" class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <span class="text-slate-500 font-medium">Running Balance:</span>
                        <span class="font-black text-base {{ $currentBalance >= 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                            Rs. {{ number_format(abs($currentBalance), 2) }} ({{ $currentBalance >= 0 ? 'Payable' : 'Advance' }})
                        </span>
                    </div>
                    <div class="space-x-2">
                        <a href="{{ route('payments.create', ['worker_id' => $worker->id]) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded text-xs">
                            <i class="fa-solid fa-money-bill-wave mr-1"></i> Pay Worker
                        </a>
                        <a href="{{ route('ledger.show', $worker) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded text-xs">
                            Full Ledger &rarr;
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                            <tr>
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Tx Number</th>
                                <th class="py-2.5 px-3">Type</th>
                                <th class="py-2.5 px-3">Description</th>
                                <th class="py-2.5 px-3 text-right">Debit (Paid)</th>
                                <th class="py-2.5 px-3 text-right">Credit (Earned)</th>
                                <th class="py-2.5 px-3 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($worker->ledgerTransactions as $tx)
                            <tr class="{{ $tx->is_reversed ? 'opacity-40 line-through bg-slate-50' : 'hover:bg-slate-50/80' }}">
                                <td class="py-2.5 px-3 text-slate-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                <td class="py-2.5 px-3 font-mono font-semibold text-slate-800">{{ $tx->transaction_number }}</td>
                                <td class="py-2.5 px-3 font-medium uppercase text-[10px]">{{ str_replace('_', ' ', $tx->transaction_type) }}</td>
                                <td class="py-2.5 px-3 text-slate-600 max-w-xs truncate">{{ $tx->description }}</td>
                                <td class="py-2.5 px-3 text-right font-medium text-rose-600">{{ $tx->debit > 0 ? 'Rs. ' . number_format($tx->debit, 2) : '-' }}</td>
                                <td class="py-2.5 px-3 text-right font-medium text-emerald-600">{{ $tx->credit > 0 ? 'Rs. ' . number_format($tx->credit, 2) : '-' }}</td>
                                <td class="py-2.5 px-3 text-right font-black text-slate-900">Rs. {{ number_format($tx->running_balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-6 text-center text-slate-400">No ledger transactions posted yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

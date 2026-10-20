@extends('layouts.app')

@section('title', 'Modify Service Request - ' . $serviceRequest->sr_number)
@section('header_title', 'Modify Service Request: ' . $serviceRequest->sr_number)
@section('header_subtitle', 'Operational Modification, Additional Jobs & Financial Overrides')

@section('content')
<div class="max-w-6xl mx-auto py-6" x-data="serviceEditWizard()">
    <form action="{{ route('service-requests.update', $serviceRequest) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Operational Info Banner -->
        <div class="p-4 rounded-2xl bg-slate-900 text-white text-xs flex items-start justify-between gap-4 shadow-sm border border-slate-800">
            <div class="flex items-start space-x-3">
                <i class="fa-solid fa-pen-to-square text-emerald-400 text-base mt-0.5"></i>
                <div>
                    <span class="font-bold text-white text-sm">Service Request Modification & Financial Overrides</span>
                    <p class="mt-0.5 text-slate-300">
                        Adjust service details, add extra jobs performed on-site, configure service & parts values (20% commission on services, 10% on parts), and manage brand after-sales warranty coverage.
                        <strong class="text-emerald-400">All ledger transactions for workers and brands will synchronize automatically upon saving.</strong>
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-slate-800 border border-slate-700 text-slate-300 shrink-0">
                {{ strtoupper(str_replace('_', ' ', $serviceRequest->status)) }}
            </span>
        </div>

        <!-- 1. Customer Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Customer Information</h3>
                </div>
                <span class="text-[11px] text-slate-500 font-mono">{{ $serviceRequest->customer?->customer_code }}</span>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Customer Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $serviceRequest->customer?->name) }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-medium">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Mobile Phone Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone', $serviceRequest->customer?->phone) }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-mono">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Customer Email</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email', $serviceRequest->customer?->email) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>
        </div>

        <!-- 2. Service Specification, Job Nature & Additional Jobs -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Service Specification & Job Classification</h3>
                </div>
                <span class="text-[11px] font-semibold text-slate-500">Configure Job Nature, Brand & Extra Work</span>
            </div>

            <div class="p-6 space-y-5 text-xs">
                <!-- Category, Subcategory, Job Selection -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Category <span class="text-rose-500">*</span></label>
                        <select name="category_id" required x-model="selectedCategory" @change="onCategoryChange()"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $serviceRequest->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Subcategory</label>
                        <select name="sub_category_id" x-model="selectedSubCategory" @change="fetchJobs()"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">All / General</option>
                            <template x-for="sub in subCategories" :key="sub.id">
                                <option :value="sub.id" :selected="sub.id == selectedSubCategory" x-text="sub.title"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Specific Job / Service <span class="text-rose-500">*</span></label>
                        <select name="service_job_id" required x-model="selectedJobId" @change="onJobSelect()"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            @foreach($jobs as $job)
                            <option value="{{ $job->id }}" {{ old('service_job_id', $serviceRequest->service_job_id) == $job->id ? 'selected' : '' }}>{{ $job->title }}</option>
                            @endforeach
                            <template x-for="job in availableJobs" :key="job.id">
                                <option :value="job.id" :selected="job.id == selectedJobId" x-text="job.title"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Job Nature / Classification Picker -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                    <label class="font-bold text-slate-800 uppercase tracking-wider text-[11px] block">
                        <i class="fa-solid fa-layer-group text-emerald-600 mr-1"></i> Job Classification & Type
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Free Installation -->
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition select-none"
                               :class="jobNature === 'free_installation' ? 'bg-amber-50/80 border-amber-400 ring-2 ring-amber-400/20' : 'bg-white border-slate-200 hover:bg-slate-100/60'">
                            <input type="radio" name="job_nature" value="free_installation" x-model="jobNature" @change="onJobNatureChange()" class="sr-only">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-gift" :class="jobNature === 'free_installation' ? 'text-amber-600' : 'text-slate-400'"></i>
                                <span class="font-bold text-slate-900 text-xs">Brand Free Installation</span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">Customer pays Rs. 0 &bull; Billed 100% to Brand Partner</p>
                        </label>

                        <!-- Standard Paid / Cash Job -->
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition select-none"
                               :class="jobNature === 'standard' ? 'bg-emerald-50/80 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white border-slate-200 hover:bg-slate-100/60'">
                            <input type="radio" name="job_nature" value="standard" x-model="jobNature" @change="onJobNatureChange()" class="sr-only">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-money-bill-wave" :class="jobNature === 'standard' ? 'text-emerald-600' : 'text-slate-400'"></i>
                                <span class="font-bold text-slate-900 text-xs">Paid Cash Job (Customer Direct)</span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">Worker collects cash &bull; 20% on service, 10% on parts</p>
                        </label>

                        <!-- Brand After-Sale Job -->
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition select-none"
                               :class="jobNature === 'brand_after_sale' ? 'bg-blue-50/80 border-blue-500 ring-2 ring-blue-500/20' : 'bg-white border-slate-200 hover:bg-slate-100/60'">
                            <input type="radio" name="job_nature" value="brand_after_sale" x-model="jobNature" @change="onJobNatureChange()" class="sr-only">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-headset" :class="jobNature === 'brand_after_sale' ? 'text-blue-600' : 'text-slate-400'"></i>
                                <span class="font-bold text-slate-900 text-xs">Brand After-Sale Service</span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">Warranty diagnostics, repairs & part replacements</p>
                        </label>
                    </div>

                    <!-- Hidden flags for backwards compatibility -->
                    <input type="hidden" name="is_free_installation" :value="jobNature === 'free_installation' ? '1' : '0'">
                    <input type="hidden" name="is_brand_job" :value="(jobNature === 'free_installation' || jobNature === 'brand_after_sale' || selectedBrandId) ? '1' : '0'">
                </div>

                <!-- Brand & Warranty Controls (If Free Installation or Brand After-Sale) -->
                <div x-show="jobNature === 'free_installation' || jobNature === 'brand_after_sale'" x-cloak class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 space-y-4">
                    <div class="flex items-center justify-between border-b border-blue-200/70 pb-2">
                        <span class="font-bold text-blue-950 uppercase tracking-wider text-[11px] flex items-center space-x-1.5">
                            <i class="fa-solid fa-building text-blue-600"></i>
                            <span x-text="jobNature === 'free_installation' ? 'Brand Installation Specifics' : 'Brand After-Sale & Warranty Controls'"></span>
                        </span>
                        <span class="text-[11px] font-semibold text-blue-800 bg-white px-2.5 py-0.5 rounded-full border border-blue-300">
                            Corporate Partner Managed
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Associated Brand <span class="text-rose-500">*</span></label>
                            <select name="brand_id" x-model="selectedBrandId" @change="recalculateAll()"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Select Brand --</option>
                                @foreach($brands as $b)
                                <option value="{{ $b->id }}" {{ old('brand_id', $serviceRequest->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Model Number</label>
                            <input type="text" name="model_number" value="{{ old('model_number', $serviceRequest->model_number) }}" placeholder="e.g. AS-18TR4RYR" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs">
                        </div>

                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Serial Number / Barcode</label>
                            <input type="text" name="serial_number" value="{{ old('serial_number', $serviceRequest->serial_number) }}" placeholder="e.g. SN-9876543210" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono text-xs">
                        </div>
                    </div>

                    <!-- Warranty Coverage Mode (Only for Brand After-Sale Job) -->
                    <div x-show="jobNature === 'brand_after_sale'" class="pt-2 border-t border-blue-200/60 space-y-2">
                        <label class="font-bold text-slate-800 uppercase tracking-wider text-[11px] block">
                            <i class="fa-solid fa-shield-halved text-blue-600 mr-1"></i> Warranty Coverage Status
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Out of Warranty -->
                            <label class="p-3 rounded-xl border cursor-pointer transition select-none flex flex-col"
                                   :class="warrantyCoverage === 'out_of_warranty' ? 'bg-white border-rose-500 ring-2 ring-rose-500/20' : 'bg-blue-50/40 border-blue-200 hover:bg-white'">
                                <input type="radio" name="warranty_coverage" value="out_of_warranty" x-model="warrantyCoverage" @change="onWarrantyChange()" class="sr-only">
                                <div class="flex items-center space-x-1.5 font-bold text-slate-900 text-xs">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span>Out of Warranty (Customer Pays)</span>
                                </div>
                                <span class="text-[10px] text-slate-500 mt-1">Customer pays 100% (Labor + Parts) &bull; Brand pays Rs. 0</span>
                            </label>

                            <!-- In Warranty -->
                            <label class="p-3 rounded-xl border cursor-pointer transition select-none flex flex-col"
                                   :class="warrantyCoverage === 'in_warranty' ? 'bg-white border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-blue-50/40 border-blue-200 hover:bg-white'">
                                <input type="radio" name="warranty_coverage" value="in_warranty" x-model="warrantyCoverage" @change="onWarrantyChange()" class="sr-only">
                                <div class="flex items-center space-x-1.5 font-bold text-slate-900 text-xs">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>In Warranty (100% Brand Covered)</span>
                                </div>
                                <span class="text-[10px] text-slate-500 mt-1">Customer pays Rs. 0 &bull; 100% Billed to Brand Partner</span>
                            </label>

                            <!-- Partial Warranty -->
                            <label class="p-3 rounded-xl border cursor-pointer transition select-none flex flex-col"
                                   :class="warrantyCoverage === 'partial_warranty' ? 'bg-white border-amber-500 ring-2 ring-amber-500/20' : 'bg-blue-50/40 border-blue-200 hover:bg-white'">
                                <input type="radio" name="warranty_coverage" value="partial_warranty" x-model="warrantyCoverage" @change="onWarrantyChange()" class="sr-only">
                                <div class="flex items-center space-x-1.5 font-bold text-slate-900 text-xs">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Partial Warranty (Split Billed)</span>
                                </div>
                                <span class="text-[10px] text-slate-500 mt-1">Split costs between Brand and Customer</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 3. Additional Job Scope & Financial Breakdown (Services 20% + Parts 10%) -->
                <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-emerald-200/70">
                        <span class="font-bold text-emerald-950 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                            <i class="fa-solid fa-plus-circle text-emerald-600 text-sm"></i>
                            <span>Work Scope Breakdown & Extra On-Site Jobs</span>
                        </span>
                        <span class="text-[11px] text-emerald-800 font-semibold bg-white px-2.5 py-0.5 rounded-full border border-emerald-300">
                            Commission Rule: 20% on Service &bull; 10% on Parts / Material
                        </span>
                    </div>

                    <!-- Additional Job Description Field -->
                    <div>
                        <label class="font-semibold text-emerald-950 block mb-1">
                            Additional Job Scope / Extra Work Description
                            <span class="text-slate-500 font-normal">(e.g. "Customer approved Evaporator leak repair + Gas Charging on-site")</span>
                        </label>
                        <input type="text" name="additional_jobs" value="{{ old('additional_jobs', $serviceRequest->additional_jobs) }}"
                               placeholder="e.g. Evaporator repair / Gas charge / Ubend replaced / Extra pipe installation..."
                               class="w-full px-3.5 py-2.5 bg-white border border-emerald-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 outline-none text-slate-800 font-medium">
                    </div>

                    <!-- Service Value & Parts Value Breakdown Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <!-- Service / Labor Value -->
                        <div class="p-3.5 bg-white rounded-xl border border-emerald-200 space-y-1.5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <label class="font-bold text-slate-800 text-xs">Service / Labor Value (Rs.)</label>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                    20% Serwizo Commission
                                </span>
                            </div>
                            <input type="number" step="0.01" name="service_value" x-model.number="serviceValue" @input="recalculateAll()"
                                   placeholder="e.g. 1000 (Visit/Labor)"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                                <span>Serwizo Margin (20%):</span>
                                <strong class="text-emerald-700 font-mono" x-text="'Rs. ' + ((serviceValue * 0.20).toFixed(2))"></strong>
                            </div>
                        </div>

                        <!-- Parts / Material Value -->
                        <div class="p-3.5 bg-white rounded-xl border border-emerald-200 space-y-1.5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <label class="font-bold text-slate-800 text-xs">Parts / Material Value (Rs.)</label>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-blue-100 text-blue-800">
                                    10% Serwizo Commission
                                </span>
                            </div>
                            <input type="number" step="0.01" name="parts_value" x-model.number="partsValue" @input="recalculateAll()"
                                   placeholder="e.g. 7000 (Gas/Spare Parts)"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                                <span>Serwizo Margin (10%):</span>
                                <strong class="text-blue-700 font-mono" x-text="'Rs. ' + ((partsValue * 0.10).toFixed(2))"></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Financial Totals, Wage Overrides & Live Ledger Preview -->
                <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Financial Totals & Ledger Overrides</span>
                        <button type="button" @click="recalculateAll(true)" class="text-[11px] text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 px-3 py-1 rounded-lg border border-emerald-200 transition">
                            <i class="fa-solid fa-calculator"></i>
                            <span>Auto-Calculate & Sync Rates</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <!-- Customer Price (Cash Collected) -->
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Customer Cash Total (Rs.) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="customer_price" x-model.number="customerPrice" @input="onManualPriceChange()" required
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Cash collected on-site by worker</span>
                        </div>

                        <!-- Brand Rate (If applicable) -->
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Brand Billed Rate (Rs.)</label>
                            <input type="number" step="0.01" name="brand_rate" x-model.number="brandRate" @input="onManualPriceChange()"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-bold text-amber-600 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Receivable billed to Brand Partner</span>
                        </div>

                        <!-- Worker Wage -->
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Worker Total Wage (Rs.) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="worker_payment" x-model.number="workerPayment" @input="onManualPriceChange()" required
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-bold text-blue-600 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Total earning credited to worker</span>
                        </div>

                        <!-- SerWizo Commission -->
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">SerWizo Commission (Rs.) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="commission_amount" x-model.number="commission" required
                                   class="w-full px-3.5 py-2 bg-emerald-50 border border-emerald-300 rounded-xl font-black text-emerald-700 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Company revenue margin</span>
                        </div>
                    </div>

                    <!-- Live Real-Time Ledger Impact Box -->
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm space-y-2">
                        <div class="flex items-center space-x-2 text-[11px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-100 pb-1.5">
                            <i class="fa-solid fa-receipt text-emerald-600"></i>
                            <span>Financial Ledger Impact Preview</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <!-- Worker Ledger Impact -->
                            <div class="p-3 rounded-lg border" :class="workerNetImpact >= 0 ? 'bg-emerald-50/60 border-emerald-200' : 'bg-amber-50/60 border-amber-200'">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">Worker Ledger Impact</span>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded" :class="workerNetImpact >= 0 ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white'">
                                        <span x-text="workerNetImpact >= 0 ? 'RECEIVABLE (+CREDIT)' : 'PAYABLE (-DEBIT)'"></span>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1" x-text="workerImpactDescription"></p>
                            </div>

                            <!-- Brand Ledger Impact -->
                            <div class="p-3 rounded-lg border" :class="brandRate > 0 ? 'bg-blue-50/60 border-blue-200' : 'bg-slate-50 border-slate-200'">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">Brand Ledger Impact</span>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded" :class="brandRate > 0 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'">
                                        <span x-text="brandRate > 0 ? 'BILLED (+DEBIT)' : 'NO BRAND IMPACT'"></span>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1" x-text="brandImpactDescription"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Location, Scheduling & Google Maps -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Location, Scheduling & Google Maps</h3>
                </div>
                <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(serviceAddress + ', ' + selectedCityName + ', Pakistan')" 
                   target="_blank" 
                   class="inline-flex items-center space-x-1.5 px-3 py-1 bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-300 rounded-xl text-xs font-semibold transition shadow-sm">
                    <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                    <span>Open in Google Maps</span>
                </a>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">City <span class="text-rose-500">*</span></label>
                    <select name="city_id" required x-model="selectedCity" @change="onCityChange()"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}" data-name="{{ $city->name }}" {{ old('city_id', $serviceRequest->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Request Source <span class="text-rose-500">*</span></label>
                    <select name="request_source" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        @foreach(['phone' => 'Phone Call', 'whatsapp' => 'WhatsApp', 'website' => 'Website Inquiry', 'brand' => 'Corporate Brand', 'worker' => 'Worker Referral', 'other' => 'Other'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('request_source', $serviceRequest->request_source) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="font-semibold text-slate-700 block mb-1">Service Address (Street, Building, Flat) <span class="text-rose-500">*</span></label>
                    <textarea name="service_address" rows="2" required x-model="serviceAddress"
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('service_address', $serviceRequest->service_address) }}</textarea>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Location Notes / Landmark</label>
                    <input type="text" name="location_notes" value="{{ old('location_notes', $serviceRequest->location_notes) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Near Roundabout, Opposite Market...">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Latitude</label>
                        <input type="text" name="latitude" value="{{ old('latitude', $serviceRequest->latitude) }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-mono"
                               placeholder="31.5204">
                    </div>
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Longitude</label>
                        <input type="text" name="longitude" value="{{ old('longitude', $serviceRequest->longitude) }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-mono"
                               placeholder="74.3587">
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Preferred Appointment Date</label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date', $serviceRequest->preferred_date?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Preferred Time Slot</label>
                    <input type="text" name="preferred_time" value="{{ old('preferred_time', $serviceRequest->preferred_time) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="e.g. 10:00 AM - 12:00 PM">
                </div>
            </div>
        </div>

        <!-- 4. Operational Status & Technician Assignment -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">4</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Status & Technician Assignment</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Current Lifecycle Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                        @foreach([
                            'new' => 'New',
                            'pending_assignment' => 'Pending Assignment',
                            'assigned' => 'Assigned',
                            'accepted' => 'Accepted (Worker Confirmed)',
                            'on_the_way' => 'On The Way',
                            'arrived' => 'Arrived at Customer Location',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'verified' => 'Verified & Approved (QA & Ledger Finalized)',
                            'cancelled' => 'Cancelled'
                        ] as $stVal => $stLbl)
                        <option value="{{ $stVal }}" {{ old('status', $serviceRequest->status) == $stVal ? 'selected' : '' }}>{{ $stLbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Assigned Technician</label>
                    <select name="current_worker_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">-- Unassigned --</option>
                        @foreach($workers as $w)
                        <option value="{{ $w->id }}" {{ old('current_worker_id', $serviceRequest->current_worker_id) == $w->id ? 'selected' : '' }}>
                            {{ $w->name }} ({{ $w->worker_code }}) &bull; {{ $w->primaryCity?->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="font-semibold text-slate-700 block mb-1">Customer Complaint / Problem Description</label>
                    <textarea name="customer_complaint" rows="2"
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('customer_complaint', $serviceRequest->customer_complaint) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="font-semibold text-slate-700 block mb-1">Special Operator Instructions for Technician</label>
                    <input type="text" name="special_instructions" value="{{ old('special_instructions', $serviceRequest->special_instructions) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="md:col-span-2">
                    <label class="font-semibold text-slate-700 block mb-1">Cancellation / Reversal Reason (If applicable)</label>
                    <input type="text" name="cancellation_reason" value="{{ old('cancellation_reason', $serviceRequest->cancellation_reason) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Reason for cancellation or manual override...">
                </div>
            </div>
        </div>

        <!-- 5. Operational Timestamps & Dates -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">5</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Operational Timestamps & Execution Dates</h3>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Assigned At</label>
                    <input type="datetime-local" name="assigned_at" 
                           value="{{ old('assigned_at', $serviceRequest->assigned_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Started / Arrival At</label>
                    <input type="datetime-local" name="started_at" 
                           value="{{ old('started_at', $serviceRequest->started_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Completed At</label>
                    <input type="datetime-local" name="completed_at" 
                           value="{{ old('completed_at', $serviceRequest->completed_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Verified & Approved At</label>
                    <input type="datetime-local" name="verified_at" 
                           value="{{ old('verified_at', $serviceRequest->verified_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('service-requests.show', $serviceRequest) }}" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-xl text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Request</span>
            </a>

            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save & Synchronize Request</span>
            </button>
        </div>
    </form>
</div>

<script>
    function serviceEditWizard() {
        return {
            selectedCategory: '{{ old('category_id', $serviceRequest->category_id) }}',
            selectedSubCategory: '{{ old('sub_category_id', $serviceRequest->sub_category_id) }}',
            subCategories: @json($subCategories),
            availableJobs: @json($jobs),
            selectedJobId: '{{ old('service_job_id', $serviceRequest->service_job_id) }}',
            
            jobNature: '{{ old('job_nature', $serviceRequest->job_nature ?: ($serviceRequest->is_free_installation ? 'free_installation' : ($serviceRequest->brand_id ? 'brand_after_sale' : 'standard'))) }}',
            warrantyCoverage: '{{ old('warranty_coverage', $serviceRequest->warranty_coverage ?: 'out_of_warranty') }}',
            selectedBrandId: '{{ old('brand_id', $serviceRequest->brand_id ?? '') }}',

            serviceValue: {{ old('service_value', $serviceRequest->service_value ?: ($serviceRequest->is_free_installation ? 0 : ($serviceRequest->customer_price ?: 0))) }},
            partsValue: {{ old('parts_value', $serviceRequest->parts_value ?: 0) }},

            brandRate: {{ old('brand_rate', $serviceRequest->brand_rate ?: ($serviceRequest->serviceJob?->brand_rate ?: 0)) }},
            customerPrice: {{ old('customer_price', $serviceRequest->customer_price ?? 0) }},
            workerPayment: {{ old('worker_payment', $serviceRequest->worker_payment ?? 0) }},
            commission: {{ old('commission_amount', $serviceRequest->commission_amount ?? 0) }},

            serviceAddress: `{{ old('service_address', $serviceRequest->service_address) }}`,
            selectedCity: '{{ old('city_id', $serviceRequest->city_id) }}',
            selectedCityName: '{{ $serviceRequest->city?->name }}',

            init() {
                this.onCityChange();
                this.recalculateAll();
            },

            onCityChange() {
                const sel = document.querySelector('select[name="city_id"]');
                if (sel && sel.selectedOptions.length > 0) {
                    this.selectedCityName = sel.selectedOptions[0].dataset.name || sel.selectedOptions[0].text;
                }
            },

            async onCategoryChange() {
                this.subCategories = [];
                this.selectedSubCategory = '';
                if (!this.selectedCategory) return;

                try {
                    const res = await fetch(`/ajax/categories/${this.selectedCategory}/sub-categories`);
                    const data = await res.json();
                    if (data.success) {
                        this.subCategories = data.data;
                    }
                } catch (e) {}

                this.fetchJobs();
            },

            async fetchJobs() {
                if (!this.selectedCategory) return;
                try {
                    let url = `/ajax/jobs-by-category?category_id=${this.selectedCategory}`;
                    if (this.selectedSubCategory) url += `&sub_category_id=${this.selectedSubCategory}`;
                    if (this.selectedCity) url += `&city_id=${this.selectedCity}`;

                    const res = await fetch(url);
                    const data = await res.json();
                    if (data.success) {
                        this.availableJobs = data.data;
                    }
                } catch (e) {}
            },

            onJobSelect() {
                const job = this.availableJobs.find(j => j.id == this.selectedJobId);
                if (job) {
                    if (job.is_free_installation) {
                        this.jobNature = 'free_installation';
                        this.brandRate = parseFloat(job.brand_rate) || 2800;
                        this.workerPayment = parseFloat(job.worker_payment) || 2000;
                        this.customerPrice = 0;
                        if (job.brand_id) this.selectedBrandId = job.brand_id;
                    } else if (job.is_brand_job) {
                        this.jobNature = 'brand_after_sale';
                        this.serviceValue = parseFloat(job.customer_price) || 1000;
                        if (job.brand_id) this.selectedBrandId = job.brand_id;
                    } else {
                        this.jobNature = 'standard';
                        this.serviceValue = parseFloat(job.customer_price) || 1000;
                    }
                    this.recalculateAll(true);
                }
            },

            onJobNatureChange() {
                if (this.jobNature === 'free_installation') {
                    this.customerPrice = 0;
                    if (!this.brandRate || this.brandRate <= 0) this.brandRate = 2800;
                    if (!this.workerPayment || this.workerPayment <= 0) this.workerPayment = 2000;
                }
                this.recalculateAll(true);
            },

            onWarrantyChange() {
                this.recalculateAll(true);
            },

            onManualPriceChange() {
                // When operator manually modifies prices, calculate commission difference
                const sv = parseFloat(this.serviceValue) || 0;
                const pv = parseFloat(this.partsValue) || 0;
                if (sv > 0 || pv > 0) {
                    this.commission = Math.round((sv * 0.20) + (pv * 0.10));
                } else {
                    const totalJobValue = (parseFloat(this.customerPrice) || 0) + (parseFloat(this.brandRate) || 0);
                    const wp = parseFloat(this.workerPayment) || 0;
                    this.commission = Math.max(0, totalJobValue - wp);
                }
            },

            recalculateAll(forceReset = false) {
                const sv = Math.max(0, parseFloat(this.serviceValue) || 0);
                const pv = Math.max(0, parseFloat(this.partsValue) || 0);
                const totalCalculatedValue = sv + pv;
                const calculatedCommission = Math.round((sv * 0.20) + (pv * 0.10));

                if (this.jobNature === 'free_installation') {
                    // Free installation: Flat Brand rate & technician wage
                    this.customerPrice = 0;
                    if (forceReset || this.brandRate <= 0) {
                        this.brandRate = (this.brandRate > 0) ? this.brandRate : 2800;
                    }
                    if (forceReset || this.workerPayment <= 0) {
                        this.workerPayment = (this.workerPayment > 0) ? this.workerPayment : 2000;
                    }
                    this.commission = Math.max(0, this.brandRate - this.workerPayment);
                } else if (this.jobNature === 'brand_after_sale') {
                    // Brand After-Sale Job Scenarios
                    if (this.warrantyCoverage === 'out_of_warranty') {
                        // Scenario A / B: Customer pays 100%, Brand pays 0
                        this.customerPrice = totalCalculatedValue;
                        this.brandRate = 0;
                        this.commission = calculatedCommission;
                        this.workerPayment = Math.max(0, totalCalculatedValue - this.commission);
                    } else if (this.warrantyCoverage === 'in_warranty') {
                        // Scenario C: Customer pays 0, Brand pays 100%
                        this.customerPrice = 0;
                        this.brandRate = totalCalculatedValue;
                        this.commission = calculatedCommission;
                        this.workerPayment = Math.max(0, totalCalculatedValue - this.commission);
                    } else if (this.warrantyCoverage === 'partial_warranty') {
                        // Scenario D: Brand covers service (or custom), Customer covers parts
                        if (forceReset || (this.customerPrice === 0 && this.brandRate === 0)) {
                            this.brandRate = sv;
                            this.customerPrice = pv;
                        }
                        this.commission = calculatedCommission;
                        this.workerPayment = Math.max(0, (this.customerPrice + this.brandRate) - this.commission);
                    }
                } else {
                    // Standard Paid Cash Job
                    this.brandRate = 0;
                    if (totalCalculatedValue > 0) {
                        this.customerPrice = totalCalculatedValue;
                        this.commission = calculatedCommission;
                        this.workerPayment = Math.max(0, totalCalculatedValue - this.commission);
                    } else {
                        const cp = parseFloat(this.customerPrice) || 0;
                        const wp = parseFloat(this.workerPayment) || 0;
                        this.commission = Math.max(0, cp - wp);
                    }
                }
            },

            get workerNetImpact() {
                const wp = parseFloat(this.workerPayment) || 0;
                const cp = parseFloat(this.customerPrice) || 0;
                return wp - cp;
            },

            get workerImpactDescription() {
                const net = this.workerNetImpact;
                const cp = parseFloat(this.customerPrice) || 0;
                const wp = parseFloat(this.workerPayment) || 0;
                if (net > 0.001) {
                    return `Worker collects Rs. ${cp.toLocaleString()} cash from customer. SerWizo owes technician Rs. ${net.toLocaleString()} (+Credit to Ledger).`;
                } else if (net < -0.001) {
                    const owes = Math.abs(net);
                    return `Worker collects Rs. ${cp.toLocaleString()} cash from customer. Technician owes SerWizo commission Rs. ${owes.toLocaleString()} (-Debit from Ledger).`;
                } else {
                    return `Balanced: Worker wage matches customer cash collected exactly (No net balance change).`;
                }
            },

            get brandImpactDescription() {
                const br = parseFloat(this.brandRate) || 0;
                if (br > 0) {
                    return `Brand will be billed Rs. ${br.toLocaleString()} (Posted as SerWizo Receivable / Debit in Brand Ledger).`;
                } else {
                    return `No billing to Brand Partner (Direct customer transaction).`;
                }
            }
        }
    }
</script>
@endsection

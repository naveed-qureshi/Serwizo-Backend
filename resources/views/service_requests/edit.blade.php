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

        <!-- 2. Service Specification, Job Nature & Multi-Item Work Order -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Service Specification & Multi-Item Work Order</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Edit work order rows, quantities, rates, extra jobs & parts</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" @click="addItem('standard')" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center space-x-1">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Add Service Row</span>
                    </button>
                    <button type="button" @click="addItem('parts')" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center space-x-1">
                        <i class="fa-solid fa-wrench text-[10px]"></i>
                        <span>Add Parts / Material</span>
                    </button>
                    <button type="button" @click="addItem('free_installation')" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center space-x-1">
                        <i class="fa-solid fa-gift text-[10px]"></i>
                        <span>Free Installation</span>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-5 text-xs">
                <!-- Category, Subcategory, Primary Job Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Primary Category <span class="text-rose-500">*</span> <span class="text-[10px] text-slate-400 font-normal">(Technician matching)</span></label>
                        <select name="category_id" required x-model="selectedCategory" @change="onCategoryChange()"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $serviceRequest->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Primary Subcategory</label>
                        <select name="sub_category_id" x-model="selectedSubCategory"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">All / General</option>
                            <template x-for="sub in subCategories" :key="sub.id">
                                <option :value="sub.id" :selected="sub.id == selectedSubCategory" x-text="sub.title"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Multi-Item Work Order Table -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                                <th class="p-3 w-8 text-center">#</th>
                                <th class="p-3 min-w-[260px]">Service Job / Spare Part Description</th>
                                <th class="p-3 min-w-[150px]">Job Nature</th>
                                <th class="p-3 w-20 text-center">Qty</th>
                                <th class="p-3 w-28 text-right">Unit Price (Rs.)</th>
                                <th class="p-3 w-28 text-right">Worker Wage (Rs.)</th>
                                <th class="p-3 w-28 text-right">Brand Rate (Rs.)</th>
                                <th class="p-3 w-28 text-right">Customer Total</th>
                                <th class="p-3 w-12 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="item.uid">
                                <tr class="hover:bg-slate-50 transition" :class="item.job_nature === 'free_installation' ? 'bg-amber-50/40' : (item.job_nature === 'parts' ? 'bg-blue-50/40' : '')">
                                    <td class="p-3 text-center font-bold text-slate-400" x-text="index + 1"></td>
                                    <td class="p-3 space-y-1">
                                        <select x-model="item.job_id" @change="onItemJobChange(item)"
                                                class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:ring-1 focus:ring-emerald-500 outline-none">
                                            <option value="">-- Choose Catalog Job or Custom --</option>
                                            <option value="custom">[+ Custom Item / Extra Material / Repair]</option>
                                            <template x-for="job in allJobs" :key="job.id">
                                                <option :value="job.id" x-text="job.title + (job.brand ? ' [' + job.brand.name + ']' : '') + ' (Rs. ' + job.customer_price + ')'"></option>
                                            </template>
                                        </select>
                                        <input type="text" x-model="item.title" placeholder="Description (e.g. 7 Seats Sofa, 10ft Copper Pipe, AC Master Wash)"
                                               class="w-full px-2 py-1 bg-white border border-slate-200 rounded text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-500 outline-none" required>
                                    </td>
                                    <td class="p-3">
                                        <select x-model="item.job_nature" @change="onItemNatureChange(item)"
                                                class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:ring-1 focus:ring-emerald-500 outline-none">
                                            <option value="standard">Cash Job (Customer Paid)</option>
                                            <option value="free_installation">Free Installation (Brand)</option>
                                            <option value="parts">Parts / Extra Material</option>
                                            <option value="brand_after_sale">Brand After-Sale</option>
                                        </select>
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" min="1" x-model.number="item.quantity" @input="recalculateItem(item)"
                                               class="w-16 px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-center font-bold focus:ring-1 focus:ring-emerald-500 outline-none">
                                    </td>
                                    <td class="p-3 text-right">
                                        <input type="number" step="1" min="0" x-model.number="item.unit_price" @input="recalculateItem(item)"
                                               :disabled="item.job_nature === 'free_installation'"
                                               class="w-24 px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-right font-bold focus:ring-1 focus:ring-emerald-500 outline-none"
                                               :class="item.job_nature === 'free_installation' ? 'bg-slate-100 text-slate-400' : 'text-slate-800'">
                                    </td>
                                    <td class="p-3 text-right">
                                        <input type="number" step="1" min="0" x-model.number="item.unit_wage" @input="recalculateItem(item)"
                                               class="w-24 px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-right font-bold text-blue-700 focus:ring-1 focus:ring-emerald-500 outline-none">
                                    </td>
                                    <td class="p-3 text-right">
                                        <input type="number" step="1" min="0" x-model.number="item.brand_rate" @input="recalculateItem(item)"
                                               :disabled="item.job_nature !== 'free_installation' && item.job_nature !== 'brand_after_sale'"
                                               class="w-24 px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-right font-bold text-amber-700 focus:ring-1 focus:ring-emerald-500 outline-none"
                                               :class="(item.job_nature !== 'free_installation' && item.job_nature !== 'brand_after_sale') ? 'bg-slate-100 text-slate-400' : ''">
                                    </td>
                                    <td class="p-3 text-right">
                                        <div class="font-black text-slate-900 text-xs" x-text="'Rs. ' + item.total_price.toLocaleString()"></div>
                                        <div class="text-[10px] text-blue-600 font-semibold" x-text="'Wage: Rs. ' + item.total_wage.toLocaleString()"></div>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeItem(index)" :disabled="items.length <= 1"
                                                class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-rose-500 hover:text-white hover:bg-rose-600 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-rose-500 transition">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Add Row Buttons -->
                <div class="flex items-center space-x-2">
                    <button type="button" @click="addItem('standard')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition border border-slate-300">
                        <i class="fa-solid fa-plus text-emerald-600 mr-1"></i> Add Another Service
                    </button>
                    <button type="button" @click="addItem('parts')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition border border-slate-300">
                        <i class="fa-solid fa-wrench text-blue-600 mr-1"></i> Add Extra Material / Spare Part
                    </button>
                </div>

                <!-- Brand Details (If Free Installation or Brand Job) -->
                <div x-show="hasBrand || hasFreeInstall" x-cloak class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200 space-y-3">
                    <div class="flex items-center space-x-2 text-amber-900 font-bold uppercase tracking-wider text-[11px]">
                        <i class="fa-solid fa-award text-amber-600"></i>
                        <span>Brand Partner & Warranty Controls</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Associated Brand</label>
                            <select name="brand_id" x-model="selectedBrandId"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold">
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

                    <div class="pt-2 border-t border-amber-200 grid grid-cols-3 gap-2">
                        <label class="p-2 bg-white rounded-lg border text-center cursor-pointer" :class="warrantyCoverage === 'out_of_warranty' ? 'border-rose-500 font-bold text-rose-700' : ''">
                            <input type="radio" name="warranty_coverage" value="out_of_warranty" x-model="warrantyCoverage" class="sr-only">
                            Out of Warranty
                        </label>
                        <label class="p-2 bg-white rounded-lg border text-center cursor-pointer" :class="warrantyCoverage === 'in_warranty' ? 'border-emerald-500 font-bold text-emerald-700' : ''">
                            <input type="radio" name="warranty_coverage" value="in_warranty" x-model="warrantyCoverage" class="sr-only">
                            In Warranty (Corporate)
                        </label>
                        <label class="p-2 bg-white rounded-lg border text-center cursor-pointer" :class="warrantyCoverage === 'partial_warranty' ? 'border-amber-500 font-bold text-amber-700' : ''">
                            <input type="radio" name="warranty_coverage" value="partial_warranty" x-model="warrantyCoverage" class="sr-only">
                            Partial Warranty
                        </label>
                    </div>
                </div>

                <!-- Financial Totals, Wage Overrides & Live Ledger Preview -->
                <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Financial Totals & Single Ledger Impact</span>
                        <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded" x-text="items.length + ' Items | Qty: ' + totalQuantity"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Customer Cash Total (Rs.) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="customer_price" x-model.number="customerPrice" @input="onManualPriceChange()" required
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Total cash collected on-site</span>
                        </div>

                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Brand Billed Rate (Rs.)</label>
                            <input type="number" step="0.01" name="brand_rate" x-model.number="brandRate" @input="onManualPriceChange()"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-bold text-amber-600 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Debited to Brand Ledger</span>
                        </div>

                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Worker Total Wage (Rs.) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="worker_payment" x-model.number="workerPayment" @input="onManualPriceChange()" required
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-bold text-blue-600 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Total technician wage</span>
                        </div>

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
                            <span>Consolidated Financial Ledger Impact Preview</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-lg border" :class="workerNetImpact >= 0 ? 'bg-emerald-50/60 border-emerald-200' : 'bg-amber-50/60 border-amber-200'">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">Worker Ledger Impact</span>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded" :class="workerNetImpact >= 0 ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white'">
                                        <span x-text="workerNetImpact >= 0 ? 'RECEIVABLE (+CREDIT)' : 'PAYABLE (-DEBIT)'"></span>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1" x-text="workerImpactDescription"></p>
                            </div>

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

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="items" :value="JSON.stringify(items)">
                <input type="hidden" name="additional_jobs" :value="JSON.stringify(items)">
                <input type="hidden" name="service_job_id" :value="primaryJobId">
                <input type="hidden" name="service_value" :value="serviceValue">
                <input type="hidden" name="parts_value" :value="partsValue">
                <input type="hidden" name="quantity" :value="totalQuantity">
                <input type="hidden" name="job_nature" :value="jobNature">
                <input type="hidden" name="is_free_installation" :value="hasFreeInstall ? '1' : '0'">
                <input type="hidden" name="is_brand_job" :value="(hasBrand || hasFreeInstall || selectedBrandId) ? '1' : '0'">
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
            allJobs: @json($jobs),
            primaryJobId: '{{ old('service_job_id', $serviceRequest->service_job_id) }}',
            
            jobNature: '{{ old('job_nature', $serviceRequest->job_nature ?: ($serviceRequest->is_free_installation ? 'free_installation' : ($serviceRequest->brand_id ? 'brand_after_sale' : 'standard'))) }}',
            warrantyCoverage: '{{ old('warranty_coverage', $serviceRequest->warranty_coverage ?: 'out_of_warranty') }}',
            selectedBrandId: '{{ old('brand_id', $serviceRequest->brand_id ?? '') }}',

            hasFreeInstall: {{ $serviceRequest->is_free_installation ? 'true' : 'false' }},
            hasBrand: {{ ($serviceRequest->is_brand_job || $serviceRequest->brand_id) ? 'true' : 'false' }},
            totalQuantity: {{ $serviceRequest->quantity ?: 1 }},

            serviceValue: {{ old('service_value', $serviceRequest->service_value ?: ($serviceRequest->is_free_installation ? 0 : ($serviceRequest->customer_price ?: 0))) }},
            partsValue: {{ old('parts_value', $serviceRequest->parts_value ?: 0) }},

            brandRate: {{ old('brand_rate', $serviceRequest->brand_rate ?: ($serviceRequest->serviceJob?->brand_rate ?: 0)) }},
            customerPrice: {{ old('customer_price', $serviceRequest->customer_price ?? 0) }},
            workerPayment: {{ old('worker_payment', $serviceRequest->worker_payment ?? 0) }},
            commission: {{ old('commission_amount', $serviceRequest->commission_amount ?? 0) }},

            serviceAddress: `{{ old('service_address', $serviceRequest->service_address) }}`,
            selectedCity: '{{ old('city_id', $serviceRequest->city_id) }}',
            selectedCityName: '{{ $serviceRequest->city?->name }}',

            items: @json($serviceRequest->items),

            init() {
                this.onCityChange();
                if (!this.items || this.items.length === 0) {
                    this.addItem('standard');
                } else {
                    this.recalculateTotals();
                }
            },

            addItem(type = 'standard') {
                const isParts = type === 'parts';
                const isFree = type === 'free_installation';
                const isBrand = type === 'brand_after_sale';
                
                const defaultPrice = isFree ? 0 : (isParts ? 500 : 1000);
                const defaultWage = isFree ? 2000 : (isParts ? 450 : 800);
                const defaultBrandRate = isFree ? 2800 : (isBrand ? 1000 : 0);

                this.items.push({
                    uid: 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                    job_id: isParts ? 'custom' : '',
                    title: isParts ? 'Extra Spare Part / Material' : '',
                    category_id: this.selectedCategory || '',
                    sub_category_id: this.selectedSubCategory || '',
                    job_nature: type,
                    quantity: 1,
                    unit_price: defaultPrice,
                    unit_wage: defaultWage,
                    brand_rate: defaultBrandRate,
                    total_price: defaultPrice,
                    total_wage: defaultWage,
                    commission: Math.max(0, (defaultPrice + defaultBrandRate) - defaultWage)
                });

                this.recalculateTotals();
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                    this.recalculateTotals();
                }
            },

            onItemJobChange(item) {
                if (item.job_id && item.job_id !== 'custom') {
                    const job = this.allJobs.find(j => j.id == item.job_id);
                    if (job) {
                        item.title = job.title;
                        item.category_id = job.category_id;
                        item.sub_category_id = job.sub_category_id;

                        if (job.is_free_installation) {
                            item.job_nature = 'free_installation';
                            item.unit_price = 0;
                            item.brand_rate = parseFloat(job.brand_rate) || 2800;
                            item.unit_wage = parseFloat(job.worker_payment) || 2000;
                            if (job.brand_id && !this.selectedBrandId) {
                                this.selectedBrandId = job.brand_id;
                            }
                        } else if (job.is_brand_job) {
                            item.job_nature = 'brand_after_sale';
                            item.unit_price = parseFloat(job.customer_price) || 1000;
                            item.unit_wage = parseFloat(job.worker_payment) || 800;
                            item.brand_rate = parseFloat(job.brand_rate) || 0;
                            if (job.brand_id && !this.selectedBrandId) {
                                this.selectedBrandId = job.brand_id;
                            }
                        } else {
                            item.job_nature = 'standard';
                            item.unit_price = parseFloat(job.customer_price) || 1000;
                            item.unit_wage = parseFloat(job.worker_payment) || 800;
                            item.brand_rate = 0;
                        }

                        if (!this.selectedCategory && job.category_id) {
                            this.selectedCategory = job.category_id;
                            this.onCategoryChange();
                        }
                    }
                } else if (item.job_id === 'custom') {
                    if (!item.title) item.title = 'Custom Service / Extra Spare Part';
                }
                this.recalculateItem(item);
            },

            onItemNatureChange(item) {
                if (item.job_nature === 'free_installation') {
                    item.unit_price = 0;
                    if (item.brand_rate <= 0) item.brand_rate = 2800;
                    if (item.unit_wage <= 0) item.unit_wage = 2000;
                } else if (item.job_nature === 'parts') {
                    if (item.unit_price <= 0) item.unit_price = 500;
                    if (item.unit_wage <= 0) item.unit_wage = Math.round(item.unit_price * 0.90);
                    item.brand_rate = 0;
                } else {
                    item.brand_rate = 0;
                }
                this.recalculateItem(item);
            },

            recalculateItem(item) {
                const qty = Math.max(1, parseInt(item.quantity) || 1);
                item.quantity = qty;
                const uPrice = Math.max(0, parseFloat(item.unit_price) || 0);
                const uWage = Math.max(0, parseFloat(item.unit_wage) || 0);
                const bRate = Math.max(0, parseFloat(item.brand_rate) || 0);

                if (item.job_nature === 'free_installation') {
                    item.unit_price = 0;
                    item.total_price = 0;
                    item.total_wage = qty * uWage;
                    item.commission = Math.max(0, (qty * bRate) - item.total_wage);
                } else if (item.job_nature === 'parts') {
                    item.total_price = qty * uPrice;
                    if (uWage > 0) {
                        item.total_wage = qty * uWage;
                    } else {
                        item.commission = Math.round(item.total_price * 0.10);
                        item.total_wage = Math.max(0, item.total_price - item.commission);
                    }
                    item.commission = Math.max(0, item.total_price - item.total_wage);
                } else {
                    item.total_price = qty * uPrice;
                    item.total_wage = qty * uWage;
                    item.commission = Math.max(0, item.total_price - item.total_wage);
                }

                this.recalculateTotals();
            },

            recalculateTotals() {
                let totalCust = 0;
                let totalWrk = 0;
                let totalBrand = 0;
                let totalComm = 0;
                let totalQ = 0;
                let sVal = 0;
                let pVal = 0;
                let freeInstall = false;
                let brandJob = false;
                let firstValidJobId = null;

                this.items.forEach(item => {
                    const qty = Math.max(1, parseInt(item.quantity) || 1);
                    totalQ += qty;

                    if (item.job_nature === 'free_installation') {
                        freeInstall = true;
                        brandJob = true;
                        totalBrand += (qty * (parseFloat(item.brand_rate) || 0));
                        totalWrk += item.total_wage;
                    } else if (item.job_nature === 'parts') {
                        pVal += item.total_price;
                        totalCust += item.total_price;
                        totalWrk += item.total_wage;
                    } else {
                        sVal += item.total_price;
                        totalCust += item.total_price;
                        totalWrk += item.total_wage;
                        if (item.job_nature === 'brand_after_sale') {
                            brandJob = true;
                            totalBrand += (qty * (parseFloat(item.brand_rate) || 0));
                        }
                    }

                    if (!firstValidJobId && item.job_id && item.job_id !== 'custom') {
                        firstValidJobId = item.job_id;
                    }
                });

                this.totalQuantity = totalQ;
                this.customerPrice = totalCust;
                this.workerPayment = totalWrk;
                this.brandRate = totalBrand;
                this.commission = Math.max(0, (totalCust + totalBrand) - totalWrk);
                this.serviceValue = sVal;
                this.partsValue = pVal;
                this.hasFreeInstall = freeInstall;
                this.hasBrand = brandJob;
                this.jobNature = freeInstall ? 'free_installation' : (brandJob ? 'brand_after_sale' : 'standard');
                if (firstValidJobId) this.primaryJobId = firstValidJobId;
            },

            onManualPriceChange() {
                const totalJobValue = (parseFloat(this.customerPrice) || 0) + (parseFloat(this.brandRate) || 0);
                const wp = parseFloat(this.workerPayment) || 0;
                this.commission = Math.max(0, totalJobValue - wp);
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
        };
    }
</script>
@endsection

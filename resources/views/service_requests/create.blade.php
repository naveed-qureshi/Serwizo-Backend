@extends('layouts.app')

@section('title', 'Book Service Request')
@section('header_title', 'Create Service Request')
@section('header_subtitle', 'Fast Operator Booking Wizard & Customer Phone Lookup')

@section('content')
<div class="max-w-4xl mx-auto py-6" x-data="serviceBookingWizard()">
    <form action="{{ route('service-requests.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Customer Phone & Identification -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Customer Information & Lookup</h3>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">Step 1: Verify Customer by Mobile</span>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <!-- Phone Search Field -->
                <div>
                    <label class="font-bold text-slate-800 block mb-1">Customer Mobile Phone Number <span class="text-rose-500">*</span></label>
                    <div class="flex items-center space-x-2 max-w-md">
                        <div class="relative flex-1">
                            <input type="text" name="customer_phone" x-model="phone" @keyup.debounce.400ms="searchCustomer()" required
                                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm font-mono font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none"
                                   placeholder="03001234567">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </div>
                        </div>
                        <button type="button" @click="searchCustomer()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold transition">
                            <i class="fa-solid fa-magnifying-glass mr-1"></i> Check
                        </button>
                    </div>
                </div>

                <!-- Existing Customer Found Card -->
                <div x-show="customerFound" x-cloak class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white uppercase">Existing Customer Found</span>
                            <span class="font-bold text-slate-900 text-sm" x-text="customerName"></span>
                            <span class="text-[11px] font-mono text-emerald-800" x-text="'(' + customerCode + ')'"></span>
                        </div>
                        <p class="text-slate-700" x-text="'Address: ' + customerAddress"></p>
                        <div class="text-[11px] text-emerald-800 font-semibold">
                            <i class="fa-solid fa-clock-rotate-left mr-1"></i> <span x-text="previousJobsCount + ' previous jobs on record'"></span>
                        </div>
                    </div>
                    <input type="hidden" name="customer_id" :value="customerId">
                </div>

                <!-- New Customer Registration Form (If not existing) -->
                <div x-show="!customerFound && phone.length >= 7" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                    <div class="flex items-center space-x-2 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <i class="fa-solid fa-user-plus text-emerald-600"></i>
                        <span>New Customer Details</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Customer Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_name" x-model="customerName" :required="!customerFound"
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                                   placeholder="Full Name">
                        </div>

                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Email Address</label>
                            <input type="email" name="customer_email" x-model="customerEmail"
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                                   placeholder="customer@gmail.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Service Job Selection & Pricing (Multi-Job Work Order) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Service Specification & Multi-Item Work Order</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Add multiple services, unit quantities (e.g. 7 sofa seats, 2 curtains), and extra parts</p>
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

            <div class="p-6 space-y-6 text-xs">
                <!-- Primary Classification for Worker Matching -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Primary Category <span class="text-rose-500">*</span> <span class="text-[10px] text-slate-400 font-normal">(Used for technician matching)</span></label>
                        <select name="category_id" required x-model="selectedCategory" @change="onCategoryChange()"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">Select Primary Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Primary Subcategory</label>
                        <select name="sub_category_id" x-model="selectedSubCategory"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">All / Any Subcategory</option>
                            <template x-for="sub in subCategories" :key="sub.id">
                                <option :value="sub.id" x-text="sub.title"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Work Order Line Items Table -->
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

                <!-- Brand Details Card (if Free Installation or Brand Job) -->
                <div x-show="hasBrand || hasFreeInstall" x-cloak class="p-4 rounded-xl bg-amber-50/60 border border-amber-200 space-y-3">
                    <div class="flex items-center space-x-2 text-amber-900 font-bold uppercase tracking-wider text-[11px]">
                        <i class="fa-solid fa-award text-amber-600"></i>
                        <span>Brand Partner & Warranty Information</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Brand Partner</label>
                            <select name="brand_id" x-model="selectedBrandId"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold">
                                <option value="">-- Select Brand --</option>
                                @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Model Number</label>
                            <input type="text" name="model_number" placeholder="e.g. AS-18TR4RYR" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Serial Number / Barcode</label>
                            <input type="text" name="serial_number" placeholder="e.g. SN-9876543210" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-mono text-xs">
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

                <!-- Consolidated Financial Overview & Single Ledger Impact -->
                <div class="p-4 bg-slate-900 text-white rounded-xl shadow-inner">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800 text-[11px]">
                        <span class="font-bold uppercase tracking-wider text-slate-300">
                            <i class="fa-solid fa-calculator text-emerald-400 mr-1.5"></i> Consolidated Work Order Summary
                        </span>
                        <span class="bg-slate-800 px-2.5 py-1 rounded-full text-emerald-400 font-mono font-bold" x-text="items.length + ' Item(s) | ' + totalQuantity + ' Total Qty'"></span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4">
                        <div class="bg-slate-800/80 p-3 rounded-lg border border-slate-700">
                            <span class="text-slate-400 font-semibold block text-[11px] mb-1">Customer Cash to Collect</span>
                            <div class="text-xl font-black text-emerald-400" x-text="'Rs. ' + customerPrice.toLocaleString()"></div>
                            <span class="text-[10px] text-slate-400" x-text="'Service: Rs. ' + serviceValue.toLocaleString() + ' | Parts: Rs. ' + partsValue.toLocaleString()"></span>
                        </div>

                        <div class="bg-slate-800/80 p-3 rounded-lg border border-slate-700">
                            <span class="text-slate-400 font-semibold block text-[11px] mb-1">Brand Corporate Billed</span>
                            <div class="text-xl font-black text-amber-400" x-text="'Rs. ' + brandRate.toLocaleString()"></div>
                            <span class="text-[10px] text-slate-400">Debited to Brand Ledger</span>
                        </div>

                        <div class="bg-slate-800/80 p-3 rounded-lg border border-slate-700">
                            <span class="text-slate-400 font-semibold block text-[11px] mb-1">Total Worker Wages</span>
                            <div class="text-xl font-black text-blue-400" x-text="'Rs. ' + workerPayment.toLocaleString()"></div>
                            <span class="text-[10px] text-slate-400">Total technician payment</span>
                        </div>

                        <div class="bg-slate-800/80 p-3 rounded-lg border border-slate-700">
                            <span class="text-slate-400 font-semibold block text-[11px] mb-1">SerWizo Net Margin</span>
                            <div class="text-xl font-black text-purple-400" x-text="'Rs. ' + commission.toLocaleString()"></div>
                            <span class="text-[10px] text-slate-400">Platform Commission</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                        <div class="flex items-center space-x-1.5">
                            <i class="fa-solid fa-circle-info text-emerald-400"></i>
                            <span><strong>Ledger Policy:</strong> Worker ledger is debited for net cash collected minus total technician wages. Brand ledger is debited for corporate billing.</span>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for backend submission -->
                <input type="hidden" name="items" :value="JSON.stringify(items)">
                <input type="hidden" name="additional_jobs" :value="JSON.stringify(items)">
                <input type="hidden" name="service_job_id" :value="primaryJobId">
                <input type="hidden" name="customer_price" :value="customerPrice">
                <input type="hidden" name="worker_payment" :value="workerPayment">
                <input type="hidden" name="commission_amount" :value="commission">
                <input type="hidden" name="brand_rate" :value="brandRate">
                <input type="hidden" name="service_value" :value="serviceValue">
                <input type="hidden" name="parts_value" :value="partsValue">
                <input type="hidden" name="quantity" :value="totalQuantity">
                <input type="hidden" name="job_nature" :value="jobNature">
                <input type="hidden" name="is_free_installation" :value="hasFreeInstall ? '1' : '0'">
                <input type="hidden" name="is_brand_job" :value="(hasBrand || hasFreeInstall || selectedBrandId) ? '1' : '0'">
            </div>
        </div>

        <!-- 3. Service Location & Scheduling -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Service Location & Scheduling</h3>
                </div>
                <template x-if="customerAddress">
                    <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(customerAddress + ', ' + selectedCityName + ', Pakistan')" 
                       target="_blank" 
                       class="inline-flex items-center space-x-1.5 px-3 py-1 bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-300 rounded-lg text-xs font-semibold transition shadow-sm">
                        <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                        <span>Locate on Google Maps</span>
                    </a>
                </template>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">City <span class="text-rose-500">*</span></label>
                    <select name="city_id" required x-model="selectedCity" @change="onCityChange()"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}" data-name="{{ $city->name }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Request Source <span class="text-rose-500">*</span></label>
                    <select name="request_source" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="phone" selected>Phone Call</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="website">Website Inquiry</option>
                        <option value="brand">Corporate Brand</option>
                        <option value="worker">Worker Referral</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="font-semibold text-slate-700 block mb-1">Service Address (Street, Building, Flat) <span class="text-rose-500">*</span></label>
                    <textarea name="service_address" rows="2" required x-model="customerAddress"
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                              placeholder="Complete address for technician arrival..."></textarea>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Location Notes / Nearby Landmark</label>
                    <input type="text" name="location_notes"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Near Roundabout, Opposite Mosque...">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Preferred Appointment Date</label>
                        <input type="date" name="preferred_date" value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Preferred Time Slot</label>
                        <input type="text" name="preferred_time" value="10:00 AM - 12:00 PM"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                               placeholder="e.g. 10:00 AM - 12:00 PM">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="font-semibold text-slate-700 block mb-1">Customer Complaint / Problem Description</label>
                    <textarea name="customer_complaint" rows="2"
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                              placeholder="Describe the issue reported by the customer..."></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="font-semibold text-slate-700 block mb-1">Special Operator Instructions for Worker</label>
                    <input type="text" name="special_instructions"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="e.g. Call before arrival, bring high ladder, test gas pressure...">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-2">
            <a href="{{ route('service-requests.index') }}" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg text-xs transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm text-xs transition">
                <i class="fa-solid fa-check mr-1.5"></i> Create Service Request
            </button>
        </div>
    </form>
</div>

<script>
    function serviceBookingWizard() {
        return {
            phone: '{{ request('customer_phone', '') }}',
            customerFound: false,
            customerId: null,
            customerName: '',
            customerCode: '',
            customerEmail: '',
            customerAddress: '',
            previousJobsCount: 0,

            selectedCategory: '{{ old('category_id') }}',
            selectedSubCategory: '{{ old('sub_category_id') }}',
            subCategories: [],
            allJobs: @json($jobs),
            primaryJobId: null,

            jobNature: 'standard',
            warrantyCoverage: 'out_of_warranty',
            selectedBrandId: '{{ old('brand_id', '') }}',

            hasFreeInstall: false,
            hasBrand: false,
            totalQuantity: 1,

            serviceValue: 1000,
            partsValue: 0,
            brandRate: 0,
            customerPrice: 1000,
            workerPayment: 800,
            commission: 200,

            selectedCity: '{{ old('city_id') }}',
            selectedCityName: '',

            items: [
                {
                    uid: 'item_' + Date.now(),
                    job_id: '',
                    title: '',
                    category_id: '',
                    sub_category_id: '',
                    job_nature: 'standard',
                    quantity: 1,
                    unit_price: 1000,
                    unit_wage: 800,
                    brand_rate: 0,
                    total_price: 1000,
                    total_wage: 800,
                    commission: 200
                }
            ],

            init() {
                if (this.phone) {
                    this.searchCustomer();
                }
                if (this.selectedCategory) {
                    this.onCategoryChange();
                }
                if (this.selectedCity) {
                    this.onCityChange();
                }
                this.recalculateTotals();
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
                this.primaryJobId = firstValidJobId || (this.allJobs.length > 0 ? this.allJobs[0].id : null);
            },

            onCityChange() {
                const sel = document.querySelector('select[name="city_id"]');
                if (sel && sel.selectedOptions.length > 0) {
                    this.selectedCityName = sel.selectedOptions[0].dataset.name || sel.selectedOptions[0].text;
                }
            },

            async searchCustomer() {
                if (!this.phone || this.phone.length < 5) return;
                try {
                    const res = await fetch(`/ajax/customers/search?phone=${encodeURIComponent(this.phone)}`);
                    const data = await res.json();
                    if (data.found) {
                        this.customerFound = true;
                        this.customerId = data.customer.id;
                        this.customerName = data.customer.name;
                        this.customerCode = data.customer.customer_code;
                        this.customerEmail = data.customer.email || '';
                        this.customerAddress = data.customer.address || '';
                        this.previousJobsCount = data.customer.previous_jobs_count;
                        if (data.customer.city_id && !this.selectedCity) {
                            this.selectedCity = data.customer.city_id;
                            this.onCityChange();
                        }
                    } else {
                        this.customerFound = false;
                        this.customerId = null;
                    }
                } catch (e) {
                    console.error("Failed customer search", e);
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
            }
        };
    }
</script>
@endsection

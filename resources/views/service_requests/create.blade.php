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

        <!-- 2. Service Job Selection & Pricing -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Select Job / Service Required</h3>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">Step 2: Service Specification & Pricing</span>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Category <span class="text-rose-500">*</span></label>
                        <select name="category_id" required x-model="selectedCategory" @change="onCategoryChange()"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Subcategory</label>
                        <select name="sub_category_id" x-model="selectedSubCategory" @change="fetchJobs()"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">All Subcategories</option>
                            <template x-for="sub in subCategories" :key="sub.id">
                                <option :value="sub.id" x-text="sub.title"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Specific Job / Service <span class="text-rose-500">*</span></label>
                        <select name="service_job_id" required x-model="selectedJobId" @change="onJobSelect()"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">Select Service Job</option>
                            <template x-for="job in availableJobs" :key="job.id">
                                <option :value="job.id" x-text="job.title + (job.is_brand_job ? ' [Corporate: Rs. ' + job.brand_rate + ']' : ' (Rs. ' + job.customer_price + ')')"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Job Nature Tabs -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <button type="button" @click="jobNature = 'free_installation'; onJobNatureChange()"
                                :class="jobNature === 'free_installation' ? 'bg-amber-500 text-white font-bold' : 'bg-white text-slate-700 border border-slate-200'"
                                class="py-2 px-1 rounded-lg text-xs transition">
                            <i class="fa-solid fa-gift mr-1"></i> Free Installation
                        </button>
                        <button type="button" @click="jobNature = 'standard'; onJobNatureChange()"
                                :class="jobNature === 'standard' ? 'bg-emerald-600 text-white font-bold' : 'bg-white text-slate-700 border border-slate-200'"
                                class="py-2 px-1 rounded-lg text-xs transition">
                            <i class="fa-solid fa-money-bill-wave mr-1"></i> Cash Job
                        </button>
                        <button type="button" @click="jobNature = 'brand_after_sale'; onJobNatureChange()"
                                :class="jobNature === 'brand_after_sale' ? 'bg-blue-600 text-white font-bold' : 'bg-white text-slate-700 border border-slate-200'"
                                class="py-2 px-1 rounded-lg text-xs transition">
                            <i class="fa-solid fa-headset mr-1"></i> Brand After-Sale
                        </button>
                    </div>
                    <input type="hidden" name="job_nature" :value="jobNature">
                    <input type="hidden" name="is_free_installation" :value="jobNature === 'free_installation' ? '1' : '0'">
                    <input type="hidden" name="is_brand_job" :value="(jobNature === 'free_installation' || jobNature === 'brand_after_sale' || selectedBrandId) ? '1' : '0'">
                </div>

                <!-- Brand Details -->
                <div x-show="jobNature === 'free_installation' || jobNature === 'brand_after_sale'" x-cloak class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Brand Partner</label>
                            <select name="brand_id" x-model="selectedBrandId" @change="recalculateAll()"
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

                    <div x-show="jobNature === 'brand_after_sale'" class="pt-2 border-t border-slate-200 grid grid-cols-3 gap-2">
                        <label class="p-2 bg-white rounded-lg border text-center cursor-pointer" :class="warrantyCoverage === 'out_of_warranty' ? 'border-rose-500 font-bold text-rose-700' : ''">
                            <input type="radio" name="warranty_coverage" value="out_of_warranty" x-model="warrantyCoverage" @change="recalculateAll()" class="sr-only">
                            Out of Warranty
                        </label>
                        <label class="p-2 bg-white rounded-lg border text-center cursor-pointer" :class="warrantyCoverage === 'in_warranty' ? 'border-emerald-500 font-bold text-emerald-700' : ''">
                            <input type="radio" name="warranty_coverage" value="in_warranty" x-model="warrantyCoverage" @change="recalculateAll()" class="sr-only">
                            In Warranty
                        </label>
                        <label class="p-2 bg-white rounded-lg border text-center cursor-pointer" :class="warrantyCoverage === 'partial_warranty' ? 'border-amber-500 font-bold text-amber-700' : ''">
                            <input type="radio" name="warranty_coverage" value="partial_warranty" x-model="warrantyCoverage" @change="recalculateAll()" class="sr-only">
                            Partial Warranty
                        </label>
                    </div>
                </div>

                <!-- Service & Parts Breakdown -->
                <div class="p-4 bg-emerald-50/60 border border-emerald-200 rounded-xl space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Service Value (Rs.) <span class="text-[10px] text-emerald-700">(20% commission)</span></label>
                            <input type="number" step="0.01" name="service_value" x-model.number="serviceValue" @input="recalculateAll()"
                                   placeholder="1000" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-bold">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Parts Value (Rs.) <span class="text-[10px] text-blue-700">(10% commission)</span></label>
                            <input type="number" step="0.01" name="parts_value" x-model.number="partsValue" @input="recalculateAll()"
                                   placeholder="0" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-bold">
                        </div>
                    </div>
                </div>

                <!-- Live Pricing Overview -->
                <div x-show="selectedJobId || serviceValue > 0" x-cloak class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1" x-text="jobNature === 'free_installation' ? 'Brand Billed Price' : 'Customer Total Bill'"></span>
                            <div class="text-lg font-black" :class="jobNature === 'free_installation' ? 'text-amber-600' : 'text-slate-900'" x-text="'Rs. ' + (jobNature === 'free_installation' ? brandRate : customerPrice)"></div>
                            <input type="hidden" name="customer_price" :value="customerPrice">
                            <input type="hidden" name="brand_rate" :value="brandRate">
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1">Worker Wage</span>
                            <div class="text-lg font-black text-blue-600" x-text="'Rs. ' + workerPayment"></div>
                            <input type="hidden" name="worker_payment" :value="workerPayment">
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1">SerWizo Margin</span>
                            <div class="text-lg font-black text-emerald-600" x-text="'Rs. ' + commission"></div>
                            <input type="hidden" name="commission_amount" :value="commission">
                        </div>
                    </div>
                </div>
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
            selectedSubCategory: '',
            subCategories: [],
            availableJobs: [],
            selectedJobId: '',

            jobNature: 'standard',
            warrantyCoverage: 'out_of_warranty',
            selectedBrandId: '',
            selectedBrandName: '',

            serviceValue: 1000,
            partsValue: 0,
            brandRate: 0,
            customerPrice: 1000,
            workerPayment: 800,
            commission: 200,

            selectedCity: '{{ old('city_id') }}',
            selectedCityName: '',

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
            },

            onCityChange() {
                const sel = document.querySelector('select[name="city_id"]');
                if (sel && sel.selectedOptions.length > 0) {
                    this.selectedCityName = sel.selectedOptions[0].dataset.name || sel.selectedOptions[0].text;
                }
                this.fetchJobs();
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
                    this.recalculateAll();
                }
            },

            onJobNatureChange() {
                this.recalculateAll();
            },

            recalculateAll() {
                const sv = Math.max(0, parseFloat(this.serviceValue) || 0);
                const pv = Math.max(0, parseFloat(this.partsValue) || 0);
                const total = sv + pv;
                const comm = Math.round((sv * 0.20) + (pv * 0.10));

                if (this.jobNature === 'free_installation') {
                    this.customerPrice = 0;
                    if (this.brandRate <= 0) this.brandRate = 2800;
                    if (this.workerPayment <= 0) this.workerPayment = 2000;
                    this.commission = Math.max(0, this.brandRate - this.workerPayment);
                } else if (this.jobNature === 'brand_after_sale') {
                    if (this.warrantyCoverage === 'out_of_warranty') {
                        this.customerPrice = total;
                        this.brandRate = 0;
                        this.commission = comm;
                        this.workerPayment = Math.max(0, total - comm);
                    } else if (this.warrantyCoverage === 'in_warranty') {
                        this.customerPrice = 0;
                        this.brandRate = total;
                        this.commission = comm;
                        this.workerPayment = Math.max(0, total - comm);
                    } else {
                        this.brandRate = sv;
                        this.customerPrice = pv;
                        this.commission = comm;
                        this.workerPayment = Math.max(0, (this.customerPrice + this.brandRate) - comm);
                    }
                } else {
                    this.brandRate = 0;
                    this.customerPrice = total;
                    this.commission = comm;
                    this.workerPayment = Math.max(0, total - comm);
                }
            }
        }
    }
</script>
@endsection

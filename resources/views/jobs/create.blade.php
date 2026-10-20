@extends('layouts.app')

@section('title', 'New Service Job')
@section('header_title', 'Create Service Catalog Title')
@section('header_subtitle', 'Define standard pricing, worker wages, and automatic commission')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <form action="{{ route('jobs.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6 text-xs" x-data="jobPricingForm()">
        @csrf
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">Service Specification</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="font-semibold text-slate-700 block mb-1">Service Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                       placeholder="e.g. AC General Service (Split / Inverter)">
            </div>

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
                <select name="sub_category_id"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">Select Subcategory</option>
                    <template x-for="sub in subCategories" :key="sub.id">
                        <option :value="sub.id" x-text="sub.title"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="font-semibold text-slate-700 block mb-1">Service Code (SKU)</label>
                <input type="text" name="code" value="{{ old('code') }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                       placeholder="e.g. APP-AC-01">
            </div>

            <div>
                <label class="font-semibold text-slate-700 block mb-1">Estimated Duration (Minutes)</label>
                <input type="number" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes', 60) }}" min="5" step="5" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="font-semibold text-slate-700 block mb-1">Service Description / Scope of Work</label>
                <textarea name="description" rows="3"
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                          placeholder="Scope of work, tools included, diagnostic checklist...">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Corporate / Brand Association (Only available for Appliances Category) -->
        <div x-show="isAppliances" x-cloak class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
            <div class="flex items-center">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_brand_job" value="1" x-model="isBrand" 
                           @change="onBrandToggle()" 
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="ml-2 font-bold text-slate-800">Is this a Corporate / Brand Job? (Free Installation &bull; Billed to Brand)</span>
                </label>
                <input type="hidden" name="is_free_installation" :value="isBrand ? '1' : '0'">
            </div>

            <p class="text-[11px] text-slate-500">
                <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i>
                For Brand Jobs, customer is charged <strong>Rs. 0</strong>. The brand is billed the job price, and technician wage is paid by SerWizo.
            </p>

            <div x-show="isBrand" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-200" x-cloak>
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Associated Brand <span class="text-rose-500">*</span></label>
                    <select name="brand_id" :required="isBrand" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">-- Select Brand --</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Brand Price (Billed to Brand) (Rs.) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="brand_rate" x-model="brandRate" @input="calculateCommission()" placeholder="e.g. 2800"
                           class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Financial Commission Calculator -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
            <h4 class="font-bold text-slate-800 uppercase tracking-wider flex items-center justify-between">
                <span>Standard Pricing & Commission Breakdown</span>
                <span class="text-[11px] text-slate-500 font-normal" x-text="isBrand ? 'Brand Price - Worker Wage = Commission' : 'Customer Price - Worker Payment = Commission'"></span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">
                        <span x-text="isBrand ? 'Customer Bill (Free)' : 'Standard Customer Price (Rs.)'"></span>
                        <span class="text-rose-500" x-show="!isBrand">*</span>
                    </label>
                    <input type="number" name="default_customer_price" x-model="customerPrice" @input="calculateCommission(); calculatePromoCommission()" min="0" step="0.01" :readonly="isBrand" required
                           :class="isBrand ? 'w-full px-3 py-2 bg-slate-100 text-slate-500 border border-slate-300 rounded-lg font-bold outline-none cursor-not-allowed' : 'w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none'"
                           placeholder="2500">
                    <span x-show="isBrand" class="text-[10px] text-emerald-600 font-semibold mt-1 block">Free for customer (Rs. 0)</span>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Standard Worker Wage (Rs.) <span class="text-rose-500">*</span></label>
                    <input type="number" name="default_worker_payment" x-model="workerPayment" @input="calculateCommission()" min="0" step="0.01" required
                           class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-blue-600 focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="2000">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">SerWizo Commission (Rs.)</label>
                    <input type="number" name="default_commission" x-model="commission" min="0" step="0.01" readonly
                           class="w-full px-3 py-2 bg-emerald-50 border border-emerald-300 rounded-lg font-black text-emerald-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-default"
                           placeholder="500">
                </div>
            </div>
        </div>

        <!-- Promotional / Discount Pricing Option -->
        <div class="p-4 bg-amber-50/60 border border-amber-200 rounded-xl space-y-3">
            <div class="flex items-center justify-between">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_promotional" value="1" x-model="isPromotional"
                           class="rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                    <span class="ml-2 font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-tag text-amber-600"></i>
                        <span>Enable Promotional / Discounted Pricing</span>
                    </span>
                </label>
                <span x-show="isPromotional" class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-amber-200 text-amber-800" x-cloak>
                    Active Campaign
                </span>
            </div>

            <p class="text-[11px] text-slate-600">
                When enabled, promotional discounted rates will be displayed on the Serwizo website and catalog, with discounted technician wages applied.
            </p>

            <div x-show="isPromotional" class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-amber-200" x-cloak>
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">
                        Promotional Customer Price (Rs.) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="promotional_customer_price" x-model="promoCustomerPrice" @input="calculatePromoCommission()" min="0" step="0.01"
                           class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg font-bold text-rose-600 focus:ring-2 focus:ring-amber-500 outline-none"
                           placeholder="e.g. 2000">
                    <div class="text-[10px] text-slate-500 mt-1" x-show="customerPrice > 0 && promoCustomerPrice > 0">
                        Discount: <span class="font-bold text-emerald-600" x-text="Math.round(((customerPrice - promoCustomerPrice) / customerPrice) * 100) + '% OFF'"></span>
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">
                        Promotional Worker Wage (Rs.) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="promotional_worker_payment" x-model="promoWorkerPayment" @input="calculatePromoCommission()" min="0" step="0.01"
                           class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg font-semibold text-amber-800 focus:ring-2 focus:ring-amber-500 outline-none"
                           placeholder="e.g. 1600">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Promotional SerWizo Net (Rs.)</label>
                    <input type="number" name="promotional_commission" x-model="promoCommission" min="0" step="0.01" readonly
                           class="w-full px-3 py-2 bg-emerald-50 border border-emerald-300 rounded-lg font-black text-emerald-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-default"
                           placeholder="400">
                </div>
            </div>
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Catalog Status <span class="text-rose-500">*</span></label>
            <select name="status" required class="w-full max-w-xs px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
            <a href="{{ route('jobs.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">Save Service</button>
        </div>
    </form>
</div>

<script>
    function jobPricingForm() {
        return {
            categories: @json($categories),
            selectedCategory: '{{ old('category_id') }}',
            subCategories: [],
            isBrand: {{ old('is_brand_job', 0) ? 'true' : 'false' }},
            brandRate: {{ old('brand_rate', 0) }},
            customerPrice: {{ old('default_customer_price', 2500) }},
            workerPayment: {{ old('default_worker_payment', 2000) }},
            commission: {{ old('default_commission', 500) }},

            isPromotional: {{ old('is_promotional', 0) ? 'true' : 'false' }},
            promoCustomerPrice: '{{ old('promotional_customer_price', '') }}',
            promoWorkerPayment: '{{ old('promotional_worker_payment', '') }}',
            promoCommission: '{{ old('promotional_commission', '') }}',

            get isAppliances() {
                if (!this.selectedCategory) return false;
                const cat = this.categories.find(c => c.id == this.selectedCategory);
                return cat ? (cat.slug === 'appliances' || cat.title.toLowerCase().includes('appliance')) : false;
            },

            init() {
                if (this.selectedCategory) {
                    this.fetchSubCategories();
                }
                if (!this.isAppliances) {
                    this.isBrand = false;
                }
                this.calculateCommission();
                this.calculatePromoCommission();
            },

            onCategoryChange() {
                this.fetchSubCategories();
                if (!this.isAppliances) {
                    this.isBrand = false;
                    this.brandRate = 0;
                    this.calculateCommission();
                }
            },

            onBrandToggle() {
                if (this.isBrand) {
                    this.customerPrice = 0;
                }
                this.calculateCommission();
            },

            calculateCommission() {
                const wp = parseFloat(this.workerPayment) || 0;
                if (this.isBrand) {
                    const br = parseFloat(this.brandRate) || 0;
                    this.commission = Math.max(0, br - wp);
                } else {
                    const cp = parseFloat(this.customerPrice) || 0;
                    this.commission = Math.max(0, cp - wp);
                }
            },

            calculatePromoCommission() {
                const pcp = parseFloat(this.promoCustomerPrice) || 0;
                const pwp = parseFloat(this.promoWorkerPayment) || 0;
                this.promoCommission = Math.max(0, pcp - pwp);
            },

            async fetchSubCategories() {
                if (!this.selectedCategory) {
                    this.subCategories = [];
                    return;
                }
                try {
                    const res = await fetch(`/ajax/categories/${this.selectedCategory}/sub-categories`);
                    const data = await res.json();
                    if (data.success) {
                        this.subCategories = data.data;
                    }
                } catch (e) {
                    console.error("Failed to load subcategories", e);
                }
            }
        }
    }
</script>
@endsection

@extends('layouts.app')

@section('title', 'Register New Worker')
@section('header_title', 'Register New Worker')
@section('header_subtitle', 'Comprehensive Multi-Section Technician Onboarding Form')

@section('content')
<div class="max-w-5xl mx-auto py-6">
    <form action="{{ route('workers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="workerForm()" autocomplete="off">
        @csrf

        <!-- Hidden traps to intercept browser autofill hijacking -->
        <input type="text" style="position:fixed;top:-9999px;opacity:0" tabindex="-1" aria-hidden="true">
        <input type="password" style="position:fixed;top:-9999px;opacity:0" tabindex="-1" aria-hidden="true">

        <!-- 1. Personal Information -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Personal Information</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Muhammad Rashid">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">CNIC / NIC Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="cnic_number" value="{{ old('cnic_number') }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="35201-1234567-1">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Mobile Phone Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="03001234567">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Email Address</label>
                    <input type="email" name="email" id="worker_email" value="{{ old('email') }}"
                           autocomplete="new-password"
                           autocorrect="off"
                           autocapitalize="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="worker@gmail.com">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" x-model="dob" @change="calculateAge()"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Age (Auto Calculated)</label>
                    <input type="text" x-model="age" readonly
                           class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-600 font-semibold cursor-not-allowed"
                           placeholder="Calculated from DOB">
                </div>

                <div class="md:col-span-2">
                    <label class="font-semibold text-slate-700 block mb-1">Current Residential Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="House #, Street, Area...">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Primary Working City <span class="text-rose-500">*</span></label>
                    <select name="primary_city_id" required x-model="primaryCityId" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ old('primary_city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Worker Service Areas -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Service Coverage Areas</h3>
            </div>
            <div class="p-6 text-xs">
                <div class="flex items-center justify-between mb-2">
                    <label class="font-semibold text-slate-700 block">Working Cities / Satellite Coverage</label>
                    <span class="text-slate-400 text-[11px]">Primary city is auto-assigned; check any additional cities</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($cities as $city)
                    <div x-data="{ isPrimary() { return String(primaryCityId) === '{{ $city->id }}'; } }">
                        <label :class="isPrimary() ? 'bg-emerald-50 border-emerald-300 ring-1 ring-emerald-400' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'"
                               class="flex items-center justify-between p-2.5 rounded-lg border cursor-pointer transition">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" name="additional_cities[]" value="{{ $city->id }}"
                                       :checked="isPrimary() || {{ in_array($city->id, old('additional_cities', [])) ? 'true' : 'false' }}"
                                       :disabled="isPrimary()"
                                       class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span :class="isPrimary() ? 'font-bold text-emerald-900' : 'text-slate-800 font-medium'">{{ $city->name }}</span>
                            </div>
                            <template x-if="isPrimary()">
                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-emerald-600 text-white shadow-xs">
                                    Primary
                                </span>
                            </template>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Professional Information -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Professional & Skills Information</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Primary Category <span class="text-rose-500">*</span></label>
                    <select name="primary_category_id" required x-model="selectedCategory" @change="fetchSubCategories()"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('primary_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Sub-Category Specialization</label>
                    <select name="primary_sub_category_id" x-model="selectedSubCategory"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">Select Sub-Category</option>
                        <template x-for="sub in subCategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.title"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Experience (Years) <span class="text-rose-500">*</span></label>
                    <input type="number" name="experience_years" value="{{ old('experience_years', 3) }}" min="0" max="50" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Skill Level <span class="text-rose-500">*</span></label>
                    <select name="skill_level" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="basic" {{ old('skill_level') == 'basic' ? 'selected' : '' }}>Basic</option>
                        <option value="intermediate" {{ old('skill_level', 'intermediate') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="experienced" {{ old('skill_level') == 'experienced' ? 'selected' : '' }}>Experienced</option>
                        <option value="expert" {{ old('skill_level') == 'expert' ? 'selected' : '' }}>Expert</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Initial Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Availability <span class="text-rose-500">*</span></label>
                    <select name="availability" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="available" {{ old('availability', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="busy" {{ old('availability') == 'busy' ? 'selected' : '' }}>Busy</option>
                        <option value="on_leave" {{ old('availability') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="unavailable" {{ old('availability') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="font-semibold text-slate-700 block mb-1">Initial Mobile App Password / PIN</label>
                    <input type="password" name="password" value="123456"
                           class="w-full max-w-xs px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Default: 123456">
                    <span class="text-[10px] text-slate-500 mt-1 block">Worker can change this upon first login to Worker App.</span>
                </div>
            </div>
        </div>

        <!-- 4. Bank Information -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">4</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Bank & Payout Information (Restricted Access)</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="e.g. Meezan Bank, HBL, Allied Bank">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Account Title</label>
                    <input type="text" name="account_title" value="{{ old('account_title') }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Account Holder Name">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Account Number">
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">IBAN Number (24 Characters)</label>
                    <input type="text" name="iban" value="{{ old('iban') }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="PK64MEZN0001020304050607">
                </div>
            </div>
        </div>

        <!-- 5. Worker References -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">5</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">References (Up to 3)</h3>
            </div>
            <div class="p-6 space-y-4 text-xs">
                @for($i = 0; $i < 3; $i++)
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Ref #{{ $i + 1 }} Name</label>
                        <input type="text" name="references[{{ $i }}][name]"
                               class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                               placeholder="Reference Name">
                    </div>
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Phone Number</label>
                        <input type="text" name="references[{{ $i }}][phone]"
                               class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                               placeholder="03001234567">
                    </div>
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Company / Relationship</label>
                        <input type="text" name="references[{{ $i }}][relationship]"
                               class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                               placeholder="e.g. Previous Employer">
                    </div>
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Additional Notes</label>
                        <input type="text" name="references[{{ $i }}][notes]"
                               class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                               placeholder="Notes...">
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- 6. Worker KYC Documents -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">6</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Worker Photos & CNIC Uploads</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Worker Photo</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="w-full text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <span class="text-[10px] text-slate-400 mt-1 block">JPG, PNG, WEBP (Max 3MB)</span>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">CNIC Front Copy</label>
                    <input type="file" name="cnic_front" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="w-full text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <span class="text-[10px] text-slate-400 mt-1 block">JPG, PNG, WEBP (Max 4MB)</span>
                </div>

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">CNIC Back Copy</label>
                    <input type="file" name="cnic_back" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="w-full text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <span class="text-[10px] text-slate-400 mt-1 block">JPG, PNG, WEBP (Max 4MB)</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4">
            <a href="{{ route('workers.index') }}" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg text-xs transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm text-xs transition">
                <i class="fa-solid fa-check mr-1.5"></i> Register Worker
            </button>
        </div>
    </form>
</div>

<script>
    function workerForm() {
        return {
            dob: '{{ old('date_of_birth') }}',
            age: '',
            primaryCityId: '{{ old('primary_city_id') }}',
            selectedCategory: '{{ old('primary_category_id') }}',
            selectedSubCategory: '{{ old('primary_sub_category_id') }}',
            subCategories: [],

            init() {
                this.calculateAge();
                if (this.selectedCategory) {
                    this.fetchSubCategories();
                }
                this.sanitizeAutofilledEmail();
            },

            sanitizeAutofilledEmail() {
                setTimeout(() => {
                    const emailEl = document.getElementById('worker_email');
                    if (emailEl && emailEl.value && !emailEl.value.includes('@')) {
                        emailEl.value = '';
                    }
                }, 150);
            },

            calculateAge() {
                if (!this.dob) {
                    this.age = '';
                    return;
                }
                const birthDate = new Date(this.dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                this.age = age > 0 ? age + ' Years' : 'Invalid Date';
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

@extends('layouts.app')

@section('title', 'Add Brand')
@section('header_title', 'Add Brand')
@section('header_subtitle', 'Register New Manufacturing Brand & Setup Portal Login')

@section('content')
<div class="max-w-4xl mx-auto pt-4 space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('brands.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Back to Brands
        </a>
    </div>

    <form method="POST" action="{{ route('brands.store') }}" class="space-y-6">
        @csrf

        <!-- Brand Information Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                <i class="fa-solid fa-building text-emerald-600"></i>
                <span>Brand & Commercial Profile</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Brand Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Hisense, Gree, Kenwood, Xiaomi" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Brand Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. HISENSE, GREE, KENWOOD" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs pt-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Contact Person Name</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Manager Operations" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 03001234567" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Official Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="brand@example.com" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="text-xs pt-2">
                <label class="block font-semibold text-slate-700 mb-1">Notes / Terms</label>
                <textarea name="notes" rows="2" placeholder="Special warranty terms, SLA, or billing notes..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Brand User Portal Access (Optional Checkbox) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-4" x-data="{ createUser: {{ old('create_user', 0) == 1 ? 'true' : 'false' }} }">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-user-lock text-emerald-600"></i>
                    <h3 class="text-sm font-bold text-slate-900">Brand Portal User Login</h3>
                </div>
                <label class="inline-flex items-center cursor-pointer text-xs">
                    <input type="checkbox" name="create_user" value="1" x-model="createUser" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="ml-2 font-semibold text-slate-700">Create Brand User Account</span>
                </label>
            </div>

            <div x-show="createUser" class="space-y-4 text-xs" x-cloak>
                <p class="text-slate-500">The user account created here will have a dedicated Brand Portal where they can view only this brand's jobs, proof pictures, serials/models, durations, and ledger statement.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">User Full Name</label>
                        <input type="text" name="user_name" value="{{ old('user_name') }}" placeholder="e.g. Brand Rep" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Login Email</label>
                        <input type="email" name="user_email" value="{{ old('user_email') }}" placeholder="rep@brand.com" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Login Password</label>
                        <input type="password" name="user_password" placeholder="Min. 6 characters" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('brands.index') }}" class="px-5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                Create Brand
            </button>
        </div>
    </form>
</div>
@endsection

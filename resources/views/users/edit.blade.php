@extends('layouts.app')

@section('title', 'Edit Staff - ' . $user->name)
@section('header_title', 'Edit Staff: ' . $user->name)
@section('header_subtitle', 'Update role assignments, status and credentials')

@section('content')
<div class="max-w-xl mx-auto py-6" x-data="{ 
    currentRole: '{{ old('role', $user->role) }}',
    showCurrentPass: false,
    showNewPass: false,
    newPass: '',
    copiedCurrent: false,
    generatePass() {
        const words = ['Serwizo', 'Staff', 'Secure', 'Portal', 'Admin', 'Access'];
        const word = words[Math.floor(Math.random() * words.length)];
        const num = Math.floor(1000 + Math.random() * 9000);
        const symbols = ['!', '@', '#', '$'];
        const sym = symbols[Math.floor(Math.random() * symbols.length)];
        this.newPass = word + '@' + num + sym;
    }
}">
    <form action="{{ route('users.update', $user) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
        @csrf
        @method('PUT')
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">User Account Details</h3>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Full Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Email Address <span class="text-rose-500">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Assigned Role <span class="text-rose-500">*</span></label>
            <select name="role" x-model="currentRole" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="operations" {{ old('role', $user->role) === 'operations' ? 'selected' : '' }}>Operations (Dispatches, Customers, Workers, Catalog)</option>
                <option value="accounts" {{ old('role', $user->role) === 'accounts' ? 'selected' : '' }}>Accounts (Worker Ledgers, Payments, Financial Reports)</option>
                <option value="brand" {{ old('role', $user->role) === 'brand' ? 'selected' : '' }}>Brand (Brand Portal &amp; Restricted Service Requests)</option>
                <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin (Full System Access)</option>
            </select>
        </div>

        <div x-show="currentRole === 'brand'" x-cloak class="p-3 bg-amber-50/70 border border-amber-200 rounded-lg space-y-1.5">
            <label class="font-bold text-amber-900 block">Select Associated Brand <span class="text-rose-500">*</span></label>
            <select name="brand_id" class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none font-semibold text-slate-800">
                <option value="">-- Choose Partner Brand --</option>
                @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ old('brand_id', $user->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }} ({{ $brand->code }})</option>
                @endforeach
            </select>
            <span class="text-[11px] text-amber-700 block">This user will ONLY see service requests, proof pictures, and ledger of this specific brand.</span>
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Phone Number</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>

        <!-- Current Stored Password Viewer -->
        @if($user->visible_password)
        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="font-bold text-slate-700 uppercase tracking-wide text-[10px]">Current Saved Password</span>
                <div class="flex items-center space-x-1.5">
                    <button type="button" @click="showCurrentPass = !showCurrentPass" class="px-2 py-0.5 bg-white border border-slate-200 rounded text-slate-600 hover:text-slate-900 transition flex items-center space-x-1">
                        <i :class="showCurrentPass ? 'fa-solid fa-eye-slash text-amber-600' : 'fa-solid fa-eye'"></i>
                        <span x-text="showCurrentPass ? 'Hide' : 'Reveal'"></span>
                    </button>
                    <button type="button" @click="navigator.clipboard.writeText('{{ addslashes($user->visible_password) }}'); copiedCurrent = true; setTimeout(() => copiedCurrent = false, 2000)" class="px-2 py-0.5 bg-white border border-slate-200 rounded text-slate-600 hover:text-slate-900 transition flex items-center space-x-1">
                        <i :class="copiedCurrent ? 'fa-solid fa-check text-emerald-600' : 'fa-regular fa-copy'"></i>
                        <span x-text="copiedCurrent ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
            </div>
            <div class="font-mono text-xs font-bold text-slate-900 bg-white p-2.5 rounded border border-slate-200 select-all tracking-wider" x-text="showCurrentPass ? '{{ addslashes($user->visible_password) }}' : '••••••••••••'"></div>
        </div>
        @endif

        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="font-semibold text-slate-700 block">Set New Password</label>
                <button type="button" @click="generatePass()" class="text-emerald-600 hover:text-emerald-700 text-[11px] font-bold">
                    <i class="fa-solid fa-wand-magic-sparkles mr-0.5"></i> Generate Password
                </button>
            </div>
            <div class="relative">
                <input :type="showNewPass ? 'text' : 'password'" name="password" x-model="newPass"
                       autocomplete="new-password"
                       class="w-full px-3 py-2 pr-10 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none font-mono text-xs"
                       placeholder="Leave blank to keep unchanged">
                <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                    <i :class="showNewPass ? 'fa-solid fa-eye-slash text-amber-600' : 'fa-solid fa-eye'"></i>
                </button>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">Once saved, this new password will be stored securely and visible to administrators.</span>
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Account Active Status <span class="text-rose-500">*</span></label>
            <select name="is_active" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="1" {{ old('is_active', $user->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $user->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Disabled</option>
            </select>
        </div>

        <div class="flex items-center justify-end space-x-2 pt-2">
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">Update Account</button>
        </div>
    </form>
</div>
@endsection

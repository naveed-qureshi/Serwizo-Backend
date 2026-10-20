@extends('layouts.app')

@section('title', 'My Profile')
@section('header_title', 'Staff Profile')
@section('header_subtitle', 'Manage your account credentials and security')

@section('content')
<div class="max-w-4xl mx-auto py-6 space-y-6">
    <!-- Account Information -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center space-x-2">
            <i class="fa-regular fa-id-badge text-emerald-600"></i>
            <span>Account Details</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Full Name</span>
                <p class="font-medium text-slate-800">{{ $user->name }}</p>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Email Address</span>
                <p class="font-medium text-slate-800">{{ $user->email }}</p>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Role / Department</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                    {{ str_replace('_', ' ', $user->role) }}
                </span>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Phone</span>
                <p class="font-medium text-slate-800">{{ $user->phone ?? 'Not provided' }}</p>
            </div>
        </div>
    </div>

    <!-- Change Password Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center space-x-2">
            <i class="fa-solid fa-key text-emerald-600"></i>
            <span>Change Password</span>
        </h3>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4 max-w-md">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Current Password</label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">New Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <button type="submit" 
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow text-sm transition">
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.guest')

@section('title', 'Admin Login')

@section('content')
<div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-700">
    <div class="bg-slate-950 p-8 text-center border-b border-slate-800">
        <div class="w-14 h-14 rounded-xl bg-emerald-500 mx-auto flex items-center justify-center text-white font-bold text-3xl shadow-lg shadow-emerald-500/30 mb-3">
            S
        </div>
        <h2 class="text-2xl font-bold text-white tracking-wide">SerWizo</h2>
        <p class="text-xs text-emerald-400 font-medium uppercase tracking-wider mt-0.5">Service Management System</p>
    </div>

    <div class="p-8">
        <h3 class="text-lg font-bold text-slate-900 mb-1">Sign in to your account</h3>
        <p class="text-xs text-slate-500 mb-6">Enter your authorized administrative credentials to continue.</p>

        @if($errors->any())
        <div class="p-3 mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-envelope text-sm"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                           placeholder="name@serwizo.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                    <span class="text-slate-600">Remember me</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition duration-200 text-sm flex items-center justify-center space-x-2">
                <span>Sign In</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center">
            <div class="text-[11px] text-slate-400">
                Authorized Personnel Only &bull; Secured Session
            </div>
        </div>
    </div>
</div>
@endsection

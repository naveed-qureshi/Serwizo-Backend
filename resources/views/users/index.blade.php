@extends('layouts.app')

@section('title', 'Staff & Roles')
@section('header_title', 'Staff & Access Management')
@section('header_subtitle', 'Authorized Operations, Accounts & Super Admin Personnel')

@section('content')
<div class="space-y-6 pt-4 text-xs">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
        <h3 class="font-bold text-slate-800 uppercase tracking-wider">System Users & Assigned Roles</h3>
        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition">
            <i class="fa-solid fa-user-plus mr-1"></i> Add Staff User
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">User Name</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Role</th>
                    <th class="py-3 px-4">Phone</th>
                    <th class="py-3 px-4">Password</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Last Login</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                <tr class="hover:bg-slate-50/80" x-data="{ 
                    showPass: false, 
                    copied: false, 
                    openReset: false, 
                    newPass: '', 
                    showNewPass: false,
                    generatePass() {
                        const words = ['Serwizo', 'Staff', 'Secure', 'Portal', 'Admin', 'Access'];
                        const word = words[Math.floor(Math.random() * words.length)];
                        const num = Math.floor(1000 + Math.random() * 9000);
                        const symbols = ['!', '@', '#', '$'];
                        const sym = symbols[Math.floor(Math.random() * symbols.length)];
                        this.newPass = word + '@' + num + sym;
                    }
                }">
                    <td class="py-3 px-4 font-bold text-slate-900">{{ $u->name }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $u->email }}</td>
                    <td class="py-3 px-4">
                        @if($u->role === 'brand')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                            Brand: {{ $u->brand?->name ?? 'Unassigned' }}
                        </span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $u->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : ($u->role === 'operations' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                            {{ str_replace('_', ' ', $u->role) }}
                        </span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-slate-600">{{ $u->phone ?? '-' }}</td>
                    
                    <!-- Password Viewer & Quick Change -->
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            @if($u->visible_password)
                            <div class="flex items-center space-x-1.5 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200">
                                <span class="font-mono text-xs text-slate-800 select-all" x-text="showPass ? '{{ addslashes($u->visible_password) }}' : '••••••••'"></span>
                                <button type="button" @click="showPass = !showPass" class="text-slate-400 hover:text-slate-700 transition" :title="showPass ? 'Hide Password' : 'Show Password'">
                                    <i :class="showPass ? 'fa-solid fa-eye-slash text-amber-600' : 'fa-solid fa-eye'"></i>
                                </button>
                                <button type="button" @click="navigator.clipboard.writeText('{{ addslashes($u->visible_password) }}'); copied = true; setTimeout(() => copied = false, 2000)" class="text-slate-400 hover:text-slate-700 transition" title="Copy to Clipboard">
                                    <i :class="copied ? 'fa-solid fa-check text-emerald-600' : 'fa-regular fa-copy'"></i>
                                </button>
                            </div>
                            @else
                            <span class="text-slate-400 italic text-[11px] bg-slate-50 px-2 py-1 rounded border border-slate-200">Legacy Hash</span>
                            @endif

                            <button type="button" @click="openReset = true; if(!newPass) generatePass();" class="px-2 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded transition flex items-center space-x-1" title="Change Password">
                                <i class="fa-solid fa-key text-[10px]"></i>
                                <span>Change</span>
                            </button>
                        </div>

                        <!-- Quick Password Reset Modal -->
                        <div x-show="openReset" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                            <div @click.away="openReset = false" class="bg-white rounded-2xl shadow-xl border border-slate-100 max-w-sm w-full p-5 space-y-4 animate-fade-in text-left">
                                <div class="flex items-center justify-between border-b pb-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                            <i class="fa-solid fa-key"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800 text-sm">Change Password</h4>
                                            <p class="text-[11px] text-slate-500">{{ $u->name }} ({{ $u->email }})</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="openReset = false" class="text-slate-400 hover:text-slate-600 p-1">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>

                                <form action="{{ route('users.password.update', $u) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="font-semibold text-slate-700 block">New Password <span class="text-rose-500">*</span></label>
                                            <button type="button" @click="generatePass()" class="text-emerald-600 hover:text-emerald-700 text-[11px] font-bold">
                                                <i class="fa-solid fa-wand-magic-sparkles mr-0.5"></i> Generate
                                            </button>
                                        </div>
                                        <div class="relative">
                                            <input :type="showNewPass ? 'text' : 'password'" name="password" x-model="newPass" required minlength="6"
                                                   class="w-full px-3 py-2 pr-10 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                            <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                                                <i :class="showNewPass ? 'fa-solid fa-eye-slash text-amber-600' : 'fa-solid fa-eye'"></i>
                                            </button>
                                        </div>
                                        <span class="text-[10px] text-slate-400 mt-1 block">The new password will be immediately visible to admins.</span>
                                    </div>

                                    <div class="flex space-x-2 pt-2 border-t">
                                        <button type="button" @click="openReset = false" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold transition text-xs">
                                            Cancel
                                        </button>
                                        <button type="submit" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold shadow-sm transition text-xs">
                                            Save Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>

                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $u->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ $u->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">{{ $u->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('users.edit', $u) }}" class="p-1.5 text-slate-600 hover:text-blue-600 rounded-md hover:bg-slate-100 transition inline-block" title="Edit User">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-6 text-center text-slate-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

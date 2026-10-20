@extends('layouts.app')

@section('title', 'Audit Logs')
@section('header_title', 'System Audit Trail')
@section('header_subtitle', 'Immutable Activity Log of Critical Actions & Role Changes')

@section('content')
<div class="space-y-6 pt-4 text-xs">

    <!-- Filter -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <select name="module" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                <option value="">All Modules</option>
                <option value="workers" {{ request('module') === 'workers' ? 'selected' : '' }}>Workers</option>
                <option value="service_requests" {{ request('module') === 'service_requests' ? 'selected' : '' }}>Service Requests</option>
                <option value="ledger" {{ request('module') === 'ledger' ? 'selected' : '' }}>Ledger & Payments</option>
                <option value="customers" {{ request('module') === 'customers' ? 'selected' : '' }}>Customers</option>
                <option value="jobs" {{ request('module') === 'jobs' ? 'selected' : '' }}>Jobs Catalog</option>
                <option value="auth" {{ request('module') === 'auth' ? 'selected' : '' }}>Authentication</option>
            </select>

            <select name="action" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                <option value="">All Actions</option>
                <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                <option value="assigned" {{ request('action') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="status_changed" {{ request('action') === 'status_changed' ? 'selected' : '' }}>Status Changed</option>
                <option value="payment_posted" {{ request('action') === 'payment_posted' ? 'selected' : '' }}>Payment Posted</option>
                <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
            </select>

            <select name="user_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                <option value="">All Staff</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-semibold">Filter Logs</button>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Staff User</th>
                        <th class="py-3 px-4">Module</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-2.5 px-4 text-slate-500 font-mono">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td class="py-2.5 px-4 font-bold text-slate-900">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="py-2.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                {{ $log->module }}
                            </span>
                        </td>
                        <td class="py-2.5 px-4 font-medium capitalize">{{ str_replace('_', ' ', $log->action) }}</td>
                        <td class="py-2.5 px-4 text-slate-700 max-w-md">{{ $log->description }}</td>
                        <td class="py-2.5 px-4 font-mono text-slate-400 text-[11px]">{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-400">No audit records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

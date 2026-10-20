@extends('layouts.app')

@section('title', 'Brands')
@section('header_title', 'Brand Management')
@section('header_subtitle', 'Authorized Brands & Corporate Free Installation Partners')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Header Actions -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('brands.index') }}" class="flex flex-1 flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search brand name, code, contact..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <select name="status" class="w-full sm:w-40 py-1.5 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition">Filter</button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('brands.index') }}" class="text-xs text-slate-500 hover:text-slate-800">Clear</a>
            @endif
        </form>

        <a href="{{ route('brands.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition shrink-0">
            <i class="fa-solid fa-plus"></i>
            <span>Add New Brand</span>
        </a>
    </div>

    <!-- Brands Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                        <th class="py-3 px-4">Brand</th>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Contact Info</th>
                        <th class="py-3 px-4 text-center">Associated Services</th>
                        <th class="py-3 px-4 text-center">Total Jobs</th>
                        <th class="py-3 px-4">Receivable Balance</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brands as $brand)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            <a href="{{ route('brands.show', $brand) }}" class="hover:text-emerald-600 flex items-center space-x-2">
                                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-black text-xs border border-emerald-200">
                                    {{ substr($brand->name, 0, 1) }}
                                </span>
                                <span>{{ $brand->name }}</span>
                            </a>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-600">{{ $brand->code }}</td>
                        <td class="py-3.5 px-4 text-slate-600">
                            @if($brand->contact_person)
                            <div class="font-medium text-slate-900">{{ $brand->contact_person }}</div>
                            @endif
                            @if($brand->phone)
                            <div class="text-[11px] text-slate-500"><i class="fa-solid fa-phone text-[10px] mr-1"></i>{{ $brand->phone }}</div>
                            @endif
                            @if($brand->email)
                            <div class="text-[11px] text-slate-400"><i class="fa-solid fa-envelope text-[10px] mr-1"></i>{{ $brand->email }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $brand->service_jobs_count ?? $brand->serviceJobs->count() }} services
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-700">{{ $brand->service_requests_count }}</td>
                        <td class="py-3.5 px-4 font-bold {{ $brand->getLedgerBalance() > 0 ? 'text-amber-600' : 'text-slate-600' }}">
                            Rs. {{ number_format($brand->getLedgerBalance(), 2) }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $brand->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $brand->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <a href="{{ route('brand-ledger.index', ['brand_id' => $brand->id]) }}" title="View Ledger" class="p-1.5 text-slate-500 hover:text-emerald-600 rounded hover:bg-slate-100">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </a>
                            <a href="{{ route('brands.edit', $brand) }}" title="Edit Brand" class="p-1.5 text-slate-500 hover:text-blue-600 rounded hover:bg-slate-100">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="{{ route('brands.show', $brand) }}" title="Brand Details" class="p-1.5 text-slate-500 hover:text-slate-900 rounded hover:bg-slate-100">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">No brands found. Click "Add New Brand" to create one.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($brands->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $brands->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', 'Sub-Categories')
@section('header_title', 'Sub-Category Management')
@section('header_subtitle', 'Specialized Service Segments Under Primary Categories')

@section('content')
<div class="space-y-6 pt-4">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-2">
            <a href="{{ route('categories.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">Categories</a>
            <a href="{{ route('sub-categories.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white">Sub-Categories</a>
        </div>

        <form method="GET" action="{{ route('sub-categories.index') }}" class="flex items-center space-x-2 text-xs">
            <select name="category_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                @endforeach
            </select>
            <a href="{{ route('sub-categories.create') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition whitespace-nowrap">
                <i class="fa-solid fa-plus mr-1"></i> New Sub-Category
            </a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                <tr>
                    <th class="py-3.5 px-4">Subcategory Title</th>
                    <th class="py-3.5 px-4">Parent Category</th>
                    <th class="py-3.5 px-4">Description</th>
                    <th class="py-3.5 px-4">Catalog Jobs</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($subCategories as $sub)
                <tr class="hover:bg-slate-50/80">
                    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $sub->title }}</td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-semibold">
                            {{ $sub->category?->title }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate">{{ $sub->description ?? '-' }}</td>
                    <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $sub->service_jobs_count }} Jobs</td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                            {{ $sub->status }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                        <a href="{{ route('sub-categories.edit', $sub) }}" class="p-1.5 text-slate-600 hover:text-blue-600 rounded-md hover:bg-slate-100 transition inline-block mr-1" title="Edit Subcategory">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                        <form action="{{ route('sub-categories.destroy', $sub) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 rounded-md hover:bg-rose-50 transition" title="Delete Subcategory">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-6 text-center text-slate-400">No subcategories found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($subCategories->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $subCategories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

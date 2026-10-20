@extends('layouts.app')

@section('title', 'Categories')
@section('header_title', 'Category Management')
@section('header_subtitle', 'Primary Service Sectors & Subcategory Hierarchies')

@section('content')
<div class="space-y-6 pt-4">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <a href="{{ route('categories.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white">Categories</a>
            <a href="{{ route('sub-categories.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">Sub-Categories</a>
        </div>
        <a href="{{ route('categories.create') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i class="fa-solid fa-plus mr-1"></i> New Category
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                <tr>
                    <th class="py-3.5 px-4">Icon & Title</th>
                    <th class="py-3.5 px-4">Active Cities Coverage</th>
                    <th class="py-3.5 px-4">Subcategories</th>
                    <th class="py-3.5 px-4">Catalog Jobs</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($categories as $cat)
                <tr class="hover:bg-slate-50/80">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid {{ $cat->icon ?? 'fa-tag' }}"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $cat->title }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ Str::limit($cat->description, 45) }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        @if($cat->cities->count() === 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                <i class="fa-solid fa-earth-asia mr-1 text-[9px]"></i> All Cities Active
                            </span>
                        @else
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @foreach($cat->cities as $city)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $city->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="py-3.5 px-4">
                        <a href="{{ route('sub-categories.index', ['category_id' => $cat->id]) }}" class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold hover:underline">
                            {{ $cat->sub_categories_count }} Sub-Services
                        </a>
                    </td>
                    <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $cat->service_jobs_count }} Jobs</td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $cat->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                            {{ $cat->status }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                        <a href="{{ route('categories.edit', $cat) }}" class="p-1.5 text-slate-600 hover:text-blue-600 rounded-md hover:bg-slate-100 transition inline-block mr-1" title="Edit Category">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                        <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 rounded-md hover:bg-rose-50 transition" title="Delete Category">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-6 text-center text-slate-400">No categories added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($categories->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

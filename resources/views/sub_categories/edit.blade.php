@extends('layouts.app')

@section('title', 'Edit Sub-Category - ' . $subCategory->title)
@section('header_title', 'Edit Sub-Category: ' . $subCategory->title)
@section('header_subtitle', 'Update Sub-Category details')

@section('content')
<div class="max-w-xl mx-auto py-6">
    <form action="{{ route('sub-categories.update', $subCategory) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
        @csrf
        @method('PUT')
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">Sub-Category Information</h3>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Parent Category <span class="text-rose-500">*</span></label>
            <select name="category_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id', $subCategory->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Subcategory Name <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $subCategory->title) }}" required
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('description', $subCategory->description) }}</textarea>
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Status <span class="text-rose-500">*</span></label>
            <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="active" {{ old('status', $subCategory->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $subCategory->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="if(confirm('Are you sure you want to delete this subcategory?')) { document.getElementById('delete-subcat-form').submit(); }" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg transition">
                <i class="fa-regular fa-trash-can mr-1"></i> Delete Sub-Category
            </button>
            <div class="flex items-center space-x-2">
                <a href="{{ route('sub-categories.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">Update Sub-Category</button>
            </div>
        </div>
    </form>

    <form id="delete-subcat-form" action="{{ route('sub-categories.destroy', $subCategory) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

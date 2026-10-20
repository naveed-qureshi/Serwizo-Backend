@extends('layouts.app')

@section('title', 'Edit Category - ' . $category->title)
@section('header_title', 'Edit Category: ' . $category->title)
@section('header_subtitle', 'Update Category title, icon and status')

@section('content')
<div class="max-w-xl mx-auto py-6">
    <form action="{{ route('categories.update', $category) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
        @csrf
        @method('PUT')
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">Category Information</h3>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Category Title <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $category->title) }}" required
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">FontAwesome Icon Class</label>
            <input type="text" name="icon" value="{{ old('icon', $category->icon) }}"
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('description', $category->description) }}</textarea>
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Status <span class="text-rose-500">*</span></label>
            <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="active" {{ old('status', $category->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $category->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        @php
            $attachedCityIds = $category->cities->pluck('id')->all();
            $defaultCoverage = (count($attachedCityIds) > 0) ? 'selected' : 'all';
        @endphp

        <!-- City Wise Activation Coverage -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3" x-data="{ coverage: '{{ old('coverage_type', $defaultCoverage) }}' }">
            <h4 class="font-bold text-slate-800 uppercase tracking-wider flex items-center justify-between">
                <span>City-Wise Activation Coverage</span>
                <span class="text-[10px] text-slate-500 font-normal">Activate in all or specific operational cities</span>
            </h4>

            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center cursor-pointer text-slate-700 font-semibold">
                    <input type="radio" name="coverage_type" value="all" x-model="coverage" class="text-emerald-600 focus:ring-emerald-500">
                    <span class="ml-2">Active in All Cities (Nationwide)</span>
                </label>
                <label class="inline-flex items-center cursor-pointer text-slate-700 font-semibold">
                    <input type="radio" name="coverage_type" value="selected" x-model="coverage" class="text-emerald-600 focus:ring-emerald-500">
                    <span class="ml-2">Selected Cities Only</span>
                </label>
            </div>

            <div x-show="coverage === 'selected'" class="pt-2 border-t border-slate-200" x-cloak>
                <label class="font-bold text-slate-800 block mb-2">Check the cities where this category is active:</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($cities as $city)
                    <label class="flex items-center space-x-2 p-2 bg-white rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                        <input type="checkbox" name="city_ids[]" value="{{ $city->id }}"
                               {{ (is_array(old('city_ids')) && in_array($city->id, old('city_ids'))) || (!old('city_ids') && in_array($city->id, $attachedCityIds)) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs font-medium text-slate-800">{{ $city->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-2 pt-2">
            <a href="{{ route('categories.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">Update Category</button>
        </div>
    </form>
</div>
@endsection

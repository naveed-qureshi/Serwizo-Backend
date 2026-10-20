@extends('layouts.app')

@section('title', 'Edit Brand - ' . $brand->name)
@section('header_title', 'Edit Brand')
@section('header_subtitle', 'Update Brand Details & Commercial Rates')

@section('content')
<div class="max-w-4xl mx-auto pt-4 space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('brands.show', $brand) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Back to {{ $brand->name }}
        </a>
    </div>

    <form method="POST" action="{{ route('brands.update', $brand) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                <i class="fa-solid fa-building text-emerald-600"></i>
                <span>Edit Brand Information</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Brand Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $brand->name) }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Brand Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $brand->code) }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white">
                        <option value="active" {{ old('status', $brand->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $brand->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs pt-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Contact Person Name</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $brand->contact_person) }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $brand->phone) }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Official Email</label>
                    <input type="email" name="email" value="{{ old('email', $brand->email) }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">
                </div>
            </div>

            <div class="text-xs pt-2">
                <label class="block font-semibold text-slate-700 mb-1">Notes / Terms</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg">{{ old('notes', $brand->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('brands.show', $brand) }}" class="px-5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

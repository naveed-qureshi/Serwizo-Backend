@extends('layouts.app')

@section('title', 'Add City')
@section('header_title', 'Add New Operating City')
@section('header_subtitle', 'Configure standard selectable service territory')

@section('content')
<div class="max-w-xl mx-auto py-6">
    <form action="{{ route('cities.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
        @csrf
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">City Information</h3>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">City Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                   placeholder="e.g. Lahore, Karachi, Islamabad">
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Province / Region</label>
            <input type="text" name="province" value="{{ old('province') }}"
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                   placeholder="e.g. Punjab, Sindh, Federal, KPK">
        </div>

        <div>
            <label class="font-semibold text-slate-700 block mb-1">Status <span class="text-rose-500">*</span></label>
            <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="flex items-center justify-end space-x-2 pt-2">
            <a href="{{ route('cities.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">Save City</button>
        </div>
    </form>
</div>
@endsection

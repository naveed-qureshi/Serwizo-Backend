@extends('layouts.app')

@section('title', 'Edit Customer - ' . $customer->name)
@section('header_title', 'Edit Customer: ' . $customer->name)
@section('header_subtitle', 'Update customer contact information and address')

@section('content')
<div class="max-w-2xl mx-auto py-6">
    <form action="{{ route('customers.update', $customer) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
        @csrf
        @method('PUT')
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">Customer Profile Information</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="font-semibold text-slate-700 block mb-1">Customer Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="font-semibold text-slate-700 block mb-1">Mobile Phone Number <span class="text-rose-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="font-semibold text-slate-700 block mb-1">Alternative Phone / WhatsApp</label>
                <input type="text" name="alt_phone" value="{{ old('alt_phone', $customer->alt_phone) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="font-semibold text-slate-700 block mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div class="sm:col-span-2">
                <label class="font-semibold text-slate-700 block mb-1">City <span class="text-rose-500">*</span></label>
                <select name="city_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                    @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ old('city_id', $customer->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="font-semibold text-slate-700 block mb-1">Primary Street Address <span class="text-rose-500">*</span></label>
                <textarea name="address" rows="2" required
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('address', $customer->address) }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="font-semibold text-slate-700 block mb-1">Customer Internal Notes</label>
                <textarea name="notes" rows="2"
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('notes', $customer->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
            <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">Update Customer</button>
        </div>
    </form>
</div>
@endsection

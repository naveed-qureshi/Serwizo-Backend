@extends('layouts.app')

@section('title', 'Job ' . $serviceRequest->sr_number . ' - ' . $brand->name)
@section('header_title', 'Installation Job Detail')
@section('header_subtitle', $serviceRequest->sr_number . ' &bull; ' . $brand->name)

@section('content')
<div class="max-w-5xl mx-auto pt-4 space-y-6" x-data="{ previewImage: null }">

    <div class="flex items-center justify-between">
        <a href="{{ route('brand-portal.jobs') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Back to Jobs List
        </a>
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase border {{ $serviceRequest->status_badge_color }}">
            {{ strtoupper(str_replace('_', ' ', $serviceRequest->status)) }}
        </span>
    </div>

    <!-- Job Header Summary -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Service Job ID</span>
                <h2 class="text-2xl font-black text-slate-900 font-mono mt-0.5">{{ $serviceRequest->sr_number }}</h2>
                <span class="text-xs text-emerald-600 font-semibold">{{ $serviceRequest->serviceJob?->title ?? 'Brand Free Installation' }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-400 block font-semibold">Start Time</span>
                    <span class="font-bold text-slate-800 block mt-0.5">
                        {{ $serviceRequest->started_at ? $serviceRequest->started_at->format('M d, Y H:i') : ($serviceRequest->assigned_at ? $serviceRequest->assigned_at->format('M d, Y H:i') : 'N/A') }}
                    </span>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-400 block font-semibold">Completion Time</span>
                    <span class="font-bold text-slate-800 block mt-0.5">
                        {{ $serviceRequest->completed_at ? $serviceRequest->completed_at->format('M d, Y H:i') : 'In Progress' }}
                    </span>
                </div>
                <div class="p-3 bg-emerald-50 rounded-lg">
                    <span class="text-emerald-600 block font-semibold">Duration Taken</span>
                    <span class="font-black text-emerald-800 block mt-0.5 text-sm">
                        {{ $serviceRequest->formatted_duration }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 text-xs">
            <!-- Customer & Location -->
            <div class="space-y-2">
                <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] text-slate-400">Customer & Site</h4>
                <div><span class="text-slate-500">Name:</span> <strong class="text-slate-900">{{ $serviceRequest->customer?->name }}</strong></div>
                <div><span class="text-slate-500">Phone:</span> <strong class="text-slate-900 font-mono">{{ $serviceRequest->customer?->phone }}</strong></div>
                <div><span class="text-slate-500">City:</span> <strong class="text-slate-900">{{ $serviceRequest->city?->name }}</strong></div>
                <div><span class="text-slate-500">Address:</span> <span class="text-slate-800">{{ $serviceRequest->service_address }}</span></div>
            </div>

            <!-- Unit & Billing -->
            <div class="space-y-2">
                <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] text-slate-400">Unit & Commercials</h4>
                <div><span class="text-slate-500">Model Number:</span> <strong class="text-slate-900 font-mono">{{ $serviceRequest->model_number ?: 'Not specified' }}</strong></div>
                <div><span class="text-slate-500">Serial Number:</span> <strong class="text-slate-900 font-mono">{{ $serviceRequest->serial_number ?: 'Not specified' }}</strong></div>
                <div><span class="text-slate-500">Quantity:</span> <strong class="text-slate-900">{{ $serviceRequest->quantity ?: 1 }} unit(s)</strong></div>
                <div><span class="text-slate-500">Brand Billed Amount:</span> <strong class="text-emerald-700 text-sm">Rs. {{ number_format($serviceRequest->brand_rate ?: $serviceRequest->customer_price, 2) }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Proof Pictures & Barcode Images -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-camera text-emerald-600"></i>
                <h3 class="text-sm font-bold text-slate-900">Proof Pictures & Job Media</h3>
            </div>
            <span class="text-xs text-slate-400 font-semibold">{{ $serviceRequest->proofs->count() }} photos uploaded</span>
        </div>

        @if($serviceRequest->proofs->count() > 0)
        @php
            $proofLabels = [
                'indoor_serial' => 'a. Indoor Serial',
                'outdoor_serial' => 'b. Outdoor Serial',
                'warranty_card' => 'c. Filled Warranty Card',
                'indoor_unit' => 'd. Indoor Installed Unit',
                'outdoor_unit' => 'e. Outdoor Installed Unit',
                'indoor_box_serial' => 'f. Indoor Box Serial Number',
                'outdoor_box_serial' => 'g. Outdoor Box Serial Number',
                'leakage_test' => 'h. Leakage Test',
                'serial_barcode' => 'Serial Barcode',
                'model_plate' => 'Model Plate',
                'completion_proof' => 'Completed Work',
            ];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($serviceRequest->proofs as $proof)
            <div class="group relative rounded-xl overflow-hidden border border-slate-200 bg-slate-50 shadow-sm cursor-pointer" @click="previewImage = '{{ $proof->image_url }}'">
                <img src="{{ $proof->image_url }}" alt="Job Proof" class="w-full h-40 object-cover group-hover:scale-105 transition duration-200" onerror="this.style.display='none'">
                <div class="p-2.5 bg-white text-[11px]">
                    <div class="font-bold text-slate-800 capitalize truncate">{{ $proofLabels[$proof->proof_type] ?? str_replace('_', ' ', $proof->proof_type) }}</div>
                    @if($proof->caption)
                    <div class="text-slate-500 truncate">{{ $proof->caption }}</div>
                    @endif
                    <div class="text-[10px] text-slate-400 mt-1">{{ $proof->created_at->format('M d, H:i') }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-8 text-center text-slate-400 text-xs">
            <i class="fa-regular fa-image text-2xl mb-1 text-slate-300"></i>
            <div>No proof photos uploaded for this job.</div>
        </div>
        @endif
    </div>

    <!-- Image Preview Modal -->
    <div x-show="previewImage" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80" x-cloak @click.away="previewImage = null">
        <div class="relative max-w-3xl w-full bg-slate-900 p-2 rounded-2xl shadow-2xl">
            <button @click="previewImage = null" class="absolute top-4 right-4 text-white bg-slate-800/80 rounded-full w-8 h-8 flex items-center justify-center hover:bg-slate-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="previewImage" class="w-full max-h-[80vh] object-contain rounded-xl">
        </div>
    </div>

</div>
@endsection

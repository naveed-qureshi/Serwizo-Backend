@extends('layouts.app')

@section('title', 'Service Request - ' . $serviceRequest->sr_number)
@section('header_title', 'Service Request: ' . $serviceRequest->sr_number)
@section('header_subtitle', ($serviceRequest->serviceJob?->title ?? 'General Service') . ' • ' . ($serviceRequest->city?->name ?? 'Lahore'))

@section('content')
<div class="space-y-6 pt-4 w-full" x-data="{ 
    dispatchModal: false, 
    statusModal: false, 
    reassignModal: false, 
    deleteModal: false, 
    uploadProofModal: false, 
    previewProofModal: false, 
    previewImgSrc: '', 
    previewImgTitle: '', 
    targetStatus: '',
    searchWorker: ''
}">

    <!-- Header & Action Status Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6 w-full">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2.5">
                <h2 class="text-2xl font-black text-slate-900 font-mono tracking-tight">{{ $serviceRequest->sr_number }}</h2>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $serviceRequest->status_badge_color }}">
                    {{ strtoupper(str_replace('_', ' ', $serviceRequest->status)) }}
                </span>
                @if($serviceRequest->is_free_installation)
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1">
                    <i class="fa-solid fa-gift text-amber-700"></i> Brand Free Installation
                </span>
                @endif
                @if($serviceRequest->brand)
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-900 border border-blue-300 flex items-center gap-1">
                    <i class="fa-solid fa-building text-blue-700"></i> {{ $serviceRequest->brand->name }}
                </span>
                @endif
                @if($serviceRequest->is_ledger_posted)
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                    <i class="fa-solid fa-check-double text-emerald-600"></i> Worker Ledger Posted
                </span>
                @endif
                @if($serviceRequest->is_brand_ledger_posted)
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200 flex items-center gap-1">
                    <i class="fa-solid fa-receipt text-purple-600"></i> Brand Billed
                </span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500 font-medium">
                <span><i class="fa-solid fa-user mr-1.5 text-slate-400"></i> <strong class="text-slate-700">{{ $serviceRequest->customer?->name }}</strong> ({{ $serviceRequest->customer?->phone }})</span>
                <span><i class="fa-solid fa-location-dot mr-1.5 text-slate-400"></i> {{ $serviceRequest->city?->name }}</span>
                <span><i class="fa-solid fa-clock mr-1.5 text-slate-400"></i> Duration: <strong class="text-slate-800">{{ $serviceRequest->formatted_duration }}</strong></span>
                <span><i class="fa-regular fa-calendar mr-1.5 text-slate-400"></i> Preferred: {{ $serviceRequest->preferred_date?->format('M d, Y') ?? 'Immediate' }} ({{ $serviceRequest->preferred_time ?? 'Standard' }})</span>
            </div>
        </div>

        <!-- Operational Action Workflow Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Super Admin / Operations Edit Button -->
            <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                <i class="fa-regular fa-pen-to-square"></i>
                <span>Modify / Edit Job</span>
            </a>

            @if(!$serviceRequest->current_worker_id && $serviceRequest->status !== 'cancelled')
            <button @click="dispatchModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-user-plus"></i>
                <span>Assign Worker</span>
            </button>
            @elseif($serviceRequest->status !== 'cancelled' && $serviceRequest->status !== 'verified')
            <button @click="dispatchModal = true" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition flex items-center gap-1.5 border border-slate-300">
                <i class="fa-solid fa-arrows-rotate text-slate-500"></i>
                <span>Reassign Worker</span>
            </button>
            @endif

            @if(in_array($serviceRequest->status, ['assigned', 'accepted', 'on_the_way', 'arrived', 'in_progress']))
                @if($serviceRequest->current_worker_id)
                <button @click="targetStatus = 'completed'; statusModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Mark Completed</span>
                </button>
                @else
                <button type="button" disabled title="Assign a technician before marking as completed"
                        class="px-4 py-2 bg-slate-200 text-slate-400 font-semibold rounded-xl text-xs cursor-not-allowed opacity-75 flex items-center gap-1.5 border border-slate-300">
                    <i class="fa-solid fa-lock text-slate-400"></i>
                    <span>Mark Completed (Worker Required)</span>
                </button>
                @endif
            @endif

            @if($serviceRequest->status === 'completed')
                @if($serviceRequest->proofs->count() > 0)
                <button @click="targetStatus = 'verified'; statusModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Verify & Approve QA</span>
                </button>
                @else
                <button type="button" disabled title="Please upload at least one proof photo before QA Approval" 
                        class="px-4 py-2 bg-slate-200 text-slate-400 font-bold rounded-xl text-xs cursor-not-allowed opacity-75 shadow-none flex items-center gap-1.5 border border-slate-300">
                    <i class="fa-solid fa-lock text-slate-400"></i>
                    <span>Verify & Approve QA (Photos Required)</span>
                </button>
                @endif
            @endif

            @if(!in_array($serviceRequest->status, ['verified', 'cancelled']))
            <button @click="targetStatus = 'cancelled'; statusModal = true" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-xl text-xs border border-rose-200 transition">
                <i class="fa-solid fa-ban mr-1"></i> Cancel
            </button>
            @endif

            <!-- Super Admin Delete Button -->
            @if(auth()->user()->isSuperAdmin())
            <button @click="deleteModal = true" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-xs transition shadow-sm" title="Delete & Auto Reverse Ledger">
                <i class="fa-solid fa-trash-can mr-1"></i> Delete
            </button>
            @endif
        </div>
    </div>

    <!-- Main Content Grid (Full Width 3-Column Responsive) -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 w-full">

        <!-- Left 2 Cols: Details, Additional Jobs, Proofs, Timeline -->
        <div class="xl:col-span-2 space-y-6">

            <!-- Service & Location Details -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2 text-sm">
                        <i class="fa-solid fa-clipboard-list text-emerald-600"></i>
                        <span>Service Details & Work Order</span>
                    </h3>
                    <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-pen text-[10px]"></i> Edit Service Details
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-semibold block mb-0.5">Primary Job Service</span>
                        <div class="font-bold text-slate-900 text-sm">{{ $serviceRequest->serviceJob?->title }}</div>
                        <span class="text-slate-500 font-medium">{{ $serviceRequest->category?->title }} &bull; {{ $serviceRequest->subCategory?->title ?? 'General' }}</span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-semibold block mb-0.5">Request Source</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-800 font-bold uppercase tracking-wider text-[11px] inline-block">
                            {{ $serviceRequest->request_source }}
                        </span>
                    </div>

                    @if($serviceRequest->brand || $serviceRequest->is_brand_job || $serviceRequest->is_free_installation)
                    <div class="sm:col-span-2 p-4 bg-gradient-to-r from-blue-50/70 to-indigo-50/70 border border-blue-200 rounded-xl space-y-3">
                        <div class="flex items-center justify-between border-b border-blue-200/70 pb-2">
                            <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px] flex items-center space-x-1.5">
                                <i class="fa-solid fa-building text-blue-600"></i>
                                <span>{{ $serviceRequest->job_nature === 'brand_after_sale' ? 'Brand After-Sale Details' : 'Brand Installation Details' }}</span>
                            </span>
                            @if($serviceRequest->brand)
                            <span class="font-bold text-blue-800 bg-white px-2.5 py-0.5 rounded-full border border-blue-200 text-xs">{{ $serviceRequest->brand->name }} ({{ $serviceRequest->brand->code }})</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-slate-700">
                            <div><span class="text-slate-400 block text-[11px]">Model:</span> <strong class="font-mono text-slate-900 text-xs">{{ $serviceRequest->model_number ?: 'N/A' }}</strong></div>
                            <div><span class="text-slate-400 block text-[11px]">Serial Number:</span> <strong class="font-mono text-slate-900 text-xs">{{ $serviceRequest->serial_number ?: 'N/A' }}</strong></div>
                            <div><span class="text-slate-400 block text-[11px]">Quantity:</span> <strong class="text-slate-900 text-xs">{{ $serviceRequest->quantity ?: 1 }} unit(s)</strong></div>
                            <div><span class="text-slate-400 block text-[11px]">Brand Rate:</span> <strong class="text-emerald-700 font-mono text-xs">Rs. {{ number_format($serviceRequest->brand_rate, 2) }}</strong></div>
                            @if($serviceRequest->warranty_coverage)
                            <div class="sm:col-span-4 pt-1 border-t border-blue-100">
                                <span class="text-slate-500 font-semibold">Warranty Status:</span>
                                <span class="ml-1 px-2 py-0.5 rounded text-[11px] font-bold {{ $serviceRequest->warranty_coverage === 'in_warranty' ? 'bg-emerald-100 text-emerald-800' : ($serviceRequest->warranty_coverage === 'partial_warranty' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ ucwords(str_replace('_', ' ', $serviceRequest->warranty_coverage)) }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Itemized Work Order Table (Multi-Job / Multiple Services / Parts) -->
                    <div class="sm:col-span-2 overflow-hidden border border-slate-200 rounded-xl">
                        <div class="p-3 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
                            <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px] flex items-center space-x-1.5">
                                <i class="fa-solid fa-list-check text-emerald-600"></i>
                                <span>Itemized Work Order & Line Items ({{ count($serviceRequest->items) }})</span>
                            </span>
                            <span class="text-[11px] font-semibold text-slate-600">Total Quantity: <strong>{{ $serviceRequest->quantity ?: count($serviceRequest->items) }}</strong></span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-600 text-[10px] uppercase font-bold border-b border-slate-200">
                                        <th class="p-2.5 text-center w-8">#</th>
                                        <th class="p-2.5">Service Job / Spare Part Description</th>
                                        <th class="p-2.5">Nature / Type</th>
                                        <th class="p-2.5 text-center w-16">Qty</th>
                                        <th class="p-2.5 text-right w-24">Unit Rate</th>
                                        <th class="p-2.5 text-right w-24">Worker Wage</th>
                                        <th class="p-2.5 text-right w-24">Brand Rate</th>
                                        <th class="p-2.5 text-right w-28">Customer Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($serviceRequest->items as $idx => $item)
                                    <tr class="hover:bg-slate-50 transition {{ ($item['job_nature'] ?? '') === 'free_installation' ? 'bg-amber-50/30' : (($item['job_nature'] ?? '') === 'parts' ? 'bg-blue-50/30' : '') }}">
                                        <td class="p-2.5 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="p-2.5 font-bold text-slate-800">
                                            {{ $item['title'] ?? 'Service' }}
                                        </td>
                                        <td class="p-2.5">
                                            @php
                                                $nature = $item['job_nature'] ?? 'standard';
                                            @endphp
                                            @if($nature === 'free_installation')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Free Install</span>
                                            @elseif($nature === 'parts')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Parts / Material</span>
                                            @elseif($nature === 'brand_after_sale')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Brand After-Sale</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Cash Job</span>
                                            @endif
                                        </td>
                                        <td class="p-2.5 text-center font-bold text-slate-900">{{ $item['quantity'] ?? 1 }}</td>
                                        <td class="p-2.5 text-right font-mono text-slate-700">Rs. {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                        <td class="p-2.5 text-right font-mono text-blue-700">Rs. {{ number_format($item['unit_wage'] ?? 0, 2) }}</td>
                                        <td class="p-2.5 text-right font-mono text-amber-700">{{ !empty($item['brand_rate']) ? 'Rs. ' . number_format($item['brand_rate'], 2) : '-' }}</td>
                                        <td class="p-2.5 text-right font-mono font-bold text-slate-900">Rs. {{ number_format($item['total_price'] ?? 0, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-900 text-white font-bold text-xs">
                                        <td colspan="3" class="p-3 uppercase tracking-wider text-slate-300">Grand Total Consolidated</td>
                                        <td class="p-3 text-center text-emerald-400">{{ $serviceRequest->quantity ?: count($serviceRequest->items) }}</td>
                                        <td class="p-3 text-right text-slate-400">-</td>
                                        <td class="p-3 text-right text-blue-400 font-mono">Rs. {{ number_format($serviceRequest->worker_payment, 2) }}</td>
                                        <td class="p-3 text-right text-amber-400 font-mono">{{ $serviceRequest->brand_rate > 0 ? 'Rs. ' . number_format($serviceRequest->brand_rate, 2) : '-' }}</td>
                                        <td class="p-3 text-right text-emerald-400 font-mono font-black text-sm">Rs. {{ number_format($serviceRequest->customer_price, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="sm:col-span-2 p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-semibold block mb-0.5">Service Address</span>
                        <p class="font-semibold text-slate-800 text-xs">{{ $serviceRequest->service_address }}</p>
                        @if($serviceRequest->location_notes)
                        <p class="text-slate-500 mt-1"><i class="fa-solid fa-map-pin text-slate-400 mr-1"></i> {{ $serviceRequest->location_notes }}</p>
                        @endif

                        @php
                            $mapsQuery = urlencode($serviceRequest->service_address . ', ' . ($serviceRequest->city?->name ?? '') . ', Pakistan');
                            $mapsUrl = ($serviceRequest->latitude && $serviceRequest->longitude)
                                ? "https://www.google.com/maps?q={$serviceRequest->latitude},{$serviceRequest->longitude}"
                                : "https://www.google.com/maps/search/?api=1&query={$mapsQuery}";
                        @endphp
                        <div class="mt-2.5">
                            <a href="{{ $mapsUrl }}" target="_blank" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white hover:bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-semibold transition shadow-sm">
                                <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                                <span>Locate on Google Maps</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-0.5"></i>
                            </a>
                        </div>
                    </div>

                    @if($serviceRequest->customer_complaint)
                    <div class="sm:col-span-2 p-3 bg-amber-50/70 border border-amber-200 rounded-xl">
                        <span class="font-bold text-amber-900 block mb-0.5">Customer Complaint / Work Scope:</span>
                        <p class="text-amber-800">{{ $serviceRequest->customer_complaint }}</p>
                    </div>
                    @endif

                    @if($serviceRequest->special_instructions)
                    <div class="sm:col-span-2 p-3 bg-blue-50/70 border border-blue-200 rounded-xl">
                        <span class="font-bold text-blue-900 block mb-0.5">Special Instructions for Technician:</span>
                        <p class="text-blue-800">{{ $serviceRequest->special_instructions }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Job Proof Images Section (List Format with Small Size Previews & No Dropdown) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">
                <div class="flex flex-wrap items-center justify-between pb-3 border-b border-slate-100 gap-3">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-camera text-emerald-600 text-base"></i>
                        <span class="text-sm font-bold text-slate-900 uppercase tracking-wider">Installation & Work Proof Photos</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $serviceRequest->proofs->count() > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $serviceRequest->proofs->count() }} Uploaded
                        </span>
                    </div>

                    <!-- Direct Multiple Photos Upload -->
                    <form action="{{ route('service-requests.proofs.upload', $serviceRequest) }}" method="POST" enctype="multipart/form-data" class="inline">
                        @csrf
                        <label class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>+ Upload Multiple Photos</span>
                            <input type="file" name="images[]" multiple accept="image/*" class="sr-only" onchange="this.form.submit()">
                        </label>
                    </form>
                </div>

                <!-- Fast Multi-Upload Notice Banner -->
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center space-x-2.5 text-slate-600">
                        <i class="fa-solid fa-circle-info text-emerald-600 text-sm"></i>
                        <span>Select all photos (e.g. 4 photos) at once — they will automatically populate as Picture 1, Picture 2, Picture 3...</span>
                    </div>
                </div>

                @php
                    $proofsMap = [];
                    $otherProofs = [];
                    foreach ($serviceRequest->proofs as $p) {
                        if (preg_match('/picture_(\d+)/', $p->proof_type ?? '', $m)) {
                            $proofsMap[(int)$m[1]] = $p;
                        } else {
                            $otherProofs[] = $p;
                        }
                    }
                    $cur = 1;
                    foreach ($otherProofs as $op) {
                        while (isset($proofsMap[$cur])) {
                            $cur++;
                        }
                        $proofsMap[$cur] = $op;
                        $cur++;
                    }

                    $slotCount = max(8, count($proofsMap));
                @endphp

                <!-- Photos List with Small Size Previews -->
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden bg-white">
                    @for($slot = 1; $slot <= $slotCount; $slot++)
                        @php
                            $slotProof = $proofsMap[$slot] ?? null;
                        @endphp
                        <div class="p-3 sm:p-4 flex items-center justify-between gap-3 hover:bg-slate-50/70 transition {{ $slotProof ? 'bg-white' : 'bg-slate-50/30' }}">
                            <!-- Thumbnail & Title -->
                            <div class="flex items-center space-x-3.5 min-w-0">
                                @if($slotProof)
                                    <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0 cursor-pointer group"
                                         @click="previewImgSrc = '{{ $slotProof->image_url }}'; previewImgTitle = 'Picture {{ $slot }}'; previewProofModal = true">
                                        <img src="{{ $slotProof->image_url }}" alt="Picture {{ $slot }}" class="w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                                        <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center text-slate-400 shrink-0">
                                        <i class="fa-solid fa-camera text-slate-300 text-base mb-1"></i>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase">Empty</span>
                                    </div>
                                @endif

                                <div class="space-y-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-extrabold text-slate-900 text-xs sm:text-sm">Picture {{ $slot }}</span>
                                        @if($slotProof)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center space-x-1">
                                                <i class="fa-solid fa-check text-[9px]"></i>
                                                <span>Uploaded</span>
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-600">
                                                Pending
                                            </span>
                                        @endif
                                    </div>

                                    @if($slotProof)
                                        <div class="text-[11px] text-slate-400 font-mono">
                                            <i class="fa-regular fa-clock mr-1"></i>{{ $slotProof->created_at?->format('d M, Y h:i A') }}
                                        </div>
                                        @if($slotProof->caption && $slotProof->caption !== 'Picture ' . $slot)
                                            <p class="text-[11px] text-slate-600 truncate max-w-md">{{ $slotProof->caption }}</p>
                                        @endif
                                    @else
                                        <p class="text-[11px] text-slate-400">No photo uploaded for slot {{ $slot }} yet</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center space-x-2 shrink-0">
                                @if($slotProof)
                                    <button type="button" @click="previewImgSrc = '{{ $slotProof->image_url }}'; previewImgTitle = 'Picture {{ $slot }}'; previewProofModal = true"
                                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition border border-slate-200">
                                        <i class="fa-solid fa-eye text-slate-500 mr-1"></i>
                                        <span class="hidden sm:inline">Enlarge</span>
                                    </button>

                                    <!-- Replace slot button -->
                                    <form action="{{ route('service-requests.proofs.upload', $serviceRequest) }}" method="POST" enctype="multipart/form-data" class="inline">
                                        @csrf
                                        <input type="hidden" name="slot_number" value="{{ $slot }}">
                                        <label class="px-2.5 py-1.5 bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-semibold rounded-lg text-xs cursor-pointer border border-slate-200 transition inline-flex items-center space-x-1">
                                            <i class="fa-solid fa-arrow-rotate-right text-xs"></i>
                                            <span class="hidden sm:inline">Replace</span>
                                            <input type="file" name="image" accept="image/*" class="sr-only" onchange="this.form.submit()">
                                        </label>
                                    </form>

                                    <!-- Delete slot button -->
                                    <form action="{{ route('service-requests.proofs.delete', $slotProof) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete Picture {{ $slot }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-white hover:bg-rose-600 rounded-lg text-xs transition" title="Delete Picture {{ $slot }}">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('service-requests.proofs.upload', $serviceRequest) }}" method="POST" enctype="multipart/form-data" class="inline">
                                        @csrf
                                        <input type="hidden" name="slot_number" value="{{ $slot }}">
                                        <label class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg text-xs cursor-pointer border border-emerald-200 transition flex items-center space-x-1.5 shadow-sm">
                                            <i class="fa-solid fa-upload text-xs"></i>
                                            <span>Upload Picture {{ $slot }}</span>
                                            <input type="file" name="image" accept="image/*" class="sr-only" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Status Transition History Timeline -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center space-x-2 text-sm">
                    <i class="fa-solid fa-timeline text-emerald-600"></i>
                    <span>Status History & Audit Trail</span>
                </h3>

                <div class="space-y-4 pl-2 border-l-2 border-slate-200">
                    @forelse($serviceRequest->statusHistory as $hist)
                    <div class="relative pl-4">
                        <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">
                                {{ strtoupper(str_replace('_', ' ', $hist->to_status)) }}
                            </span>
                            <span class="text-slate-400">&bull; {{ $hist->created_at->format('M d, Y H:i:s') }}</span>
                        </div>
                        @if($hist->notes)
                        <p class="text-slate-600 mt-0.5">{{ $hist->notes }}</p>
                        @endif
                        <div class="text-[10px] text-slate-400 mt-0.5">
                            By: {{ $hist->changedBy?->name ?? ($hist->worker?->name ?? 'System Event') }}
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-400 pl-4">No status changes logged.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right 1 Col: Financials, Worker Card, Customer Card, Quick Actions -->
        <div class="space-y-6">

            <!-- Financial Breakdown Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2 text-sm">
                        <i class="fa-solid fa-coins text-emerald-600"></i>
                        <span>Financial Breakdown</span>
                    </h3>
                    <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-bold">
                        Modify
                    </a>
                </div>

                <dl class="space-y-3">
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                        <dt class="text-slate-600 font-medium">Customer Bill Total:</dt>
                        <dd class="font-black text-slate-900 text-base">Rs. {{ number_format($serviceRequest->customer_price, 2) }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                        <dt class="text-slate-600 font-medium">Worker Wage Payable:</dt>
                        <dd class="font-black text-blue-600 text-base">Rs. {{ number_format($serviceRequest->worker_payment, 2) }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                        <dt class="text-slate-600 font-medium">SerWizo Commission:</dt>
                        <dd class="font-black text-emerald-600 text-base">Rs. {{ number_format($serviceRequest->commission_amount, 2) }}</dd>
                    </div>
                    @if($serviceRequest->brand_rate > 0)
                    <div class="flex justify-between items-center py-1.5 bg-blue-50/50 p-2 rounded-lg">
                        <dt class="text-blue-900 font-medium">Brand Billing (Receivable):</dt>
                        <dd class="font-black text-blue-800 text-sm">Rs. {{ number_format($serviceRequest->brand_rate, 2) }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Ledger Transactions Card (Audit Trail) -->
            @if($serviceRequest->ledgerTransactions->count() > 0 || $serviceRequest->brandLedgers->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-3 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2 text-sm">
                        <i class="fa-solid fa-book-bookmark text-emerald-600"></i>
                        <span>Ledger Audit Trail</span>
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ in_array($serviceRequest->status, ['completed', 'verified']) ? 'bg-emerald-100 text-emerald-800' : ($serviceRequest->status === 'cancelled' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-800') }}">
                        {{ in_array($serviceRequest->status, ['completed', 'verified']) ? 'Active Postings' : ($serviceRequest->status === 'cancelled' ? 'Zero Net Impact' : 'Pending Verification') }}
                    </span>
                </div>

                @if($serviceRequest->ledgerTransactions->count() > 0)
                <div class="space-y-1.5">
                    <span class="text-slate-400 font-semibold block text-[11px]">Worker Ledger:</span>
                    @foreach($serviceRequest->ledgerTransactions as $lt)
                    <div class="p-2 rounded-lg border {{ $lt->is_reversed ? 'bg-slate-50 border-slate-200 text-slate-400 line-through' : ($lt->transaction_type === 'reversal' ? 'bg-rose-50/50 border-rose-200 text-rose-800' : 'bg-emerald-50/50 border-emerald-200 text-emerald-900') }} flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold">{{ $lt->transaction_number }}</span>
                            <span class="text-[10px] block opacity-80 capitalize">{{ str_replace('_', ' ', $lt->transaction_type) }} &bull; {{ $lt->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold font-mono">{{ $lt->credit > 0 ? '+Rs. ' . number_format($lt->credit, 2) : '-Rs. ' . number_format($lt->debit, 2) }}</span>
                            @if($lt->is_reversed)
                            <span class="block text-[9px] font-bold text-rose-600 uppercase no-underline">Reversed</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($serviceRequest->brandLedgers->count() > 0)
                <div class="space-y-1.5 pt-1">
                    <span class="text-slate-400 font-semibold block text-[11px]">Brand Ledger:</span>
                    @foreach($serviceRequest->brandLedgers as $bl)
                    <div class="p-2 rounded-lg border {{ $bl->is_reversed ? 'bg-slate-50 border-slate-200 text-slate-400 line-through' : ($bl->transaction_type === 'reversal' ? 'bg-rose-50/50 border-rose-200 text-rose-800' : 'bg-blue-50/50 border-blue-200 text-blue-900') }} flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold">{{ $bl->transaction_number }}</span>
                            <span class="text-[10px] block opacity-80 capitalize">{{ str_replace('_', ' ', $bl->transaction_type) }} &bull; {{ $bl->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold font-mono">{{ $bl->debit > 0 ? 'Rs. ' . number_format($bl->debit, 2) : '-Rs. ' . number_format($bl->credit, 2) }}</span>
                            @if($bl->is_reversed)
                            <span class="block text-[9px] font-bold text-rose-600 uppercase no-underline">Reversed</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- Assigned Worker Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2 text-sm">
                        <i class="fa-solid fa-user-gear text-emerald-600"></i>
                        <span>Assigned Technician</span>
                    </h3>
                    @if($serviceRequest->currentWorker)
                    <button @click="dispatchModal = true" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-bold">
                        Reassign
                    </button>
                    @endif
                </div>

                @if($serviceRequest->currentWorker)
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-full bg-slate-200 overflow-hidden shrink-0 border border-slate-300 flex items-center justify-center">
                        @if($serviceRequest->currentWorker->photo_path)
                        <img src="{{ $serviceRequest->currentWorker->photo_url }}" alt="{{ $serviceRequest->currentWorker->name }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'font-bold text-sm text-slate-600\'>{{ strtoupper(substr($serviceRequest->currentWorker->name, 0, 1)) }}</span>';">
                        @else
                        <i class="fa-solid fa-user text-slate-400 text-lg"></i>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('workers.show', $serviceRequest->currentWorker) }}" class="font-bold text-slate-900 hover:text-emerald-600 text-sm block">
                            {{ $serviceRequest->currentWorker->name }}
                        </a>
                        <span class="text-slate-500 font-mono">{{ $serviceRequest->currentWorker->worker_code }}</span>
                    </div>
                </div>
                <div class="space-y-1.5 text-slate-600 pt-1">
                    <div><i class="fa-solid fa-phone mr-2 text-slate-400"></i> {{ $serviceRequest->currentWorker->phone }}</div>
                    <div><i class="fa-solid fa-location-dot mr-2 text-slate-400"></i> {{ $serviceRequest->currentWorker->primaryCity?->name }}</div>
                </div>
                @else
                <div class="p-5 rounded-xl bg-amber-50 border border-amber-200 text-center space-y-2.5">
                    <p class="text-amber-900 font-semibold text-xs">No technician assigned yet.</p>
                    <button @click="dispatchModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition text-xs shadow-sm w-full">
                        Select & Dispatch Worker
                    </button>
                </div>
                @endif
            </div>

            <!-- Customer Profile Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 text-xs">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center space-x-2 text-sm">
                    <i class="fa-solid fa-user text-emerald-600"></i>
                    <span>Customer Details</span>
                </h3>

                <div>
                    <a href="{{ route('customers.show', $serviceRequest->customer) }}" class="font-bold text-slate-900 hover:text-emerald-600 text-sm block">
                        {{ $serviceRequest->customer?->name }}
                    </a>
                    <span class="text-slate-500 font-mono">{{ $serviceRequest->customer?->customer_code }}</span>
                </div>
                <div class="space-y-1.5 text-slate-600">
                    <div><i class="fa-solid fa-phone mr-2 text-slate-400"></i> {{ $serviceRequest->customer?->phone }}</div>
                    @if($serviceRequest->customer?->alt_phone)
                    <div><i class="fa-brands fa-whatsapp mr-2 text-emerald-500"></i> {{ $serviceRequest->customer?->alt_phone }}</div>
                    @endif
                    <div><i class="fa-solid fa-location-dot mr-2 text-slate-400"></i> {{ $serviceRequest->customer?->address }}</div>
                </div>
            </div>

        </div>

    </div>

    <!-- Dispatch / Suitable Worker Selection Modal with Instant Search -->
    <div x-show="dispatchModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 space-y-4 text-xs border border-slate-200" @click.away="dispatchModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Dispatch Worker for {{ $serviceRequest->sr_number }}</h3>
                    <p class="text-slate-500">City: <strong>{{ $serviceRequest->city?->name }}</strong> &bull; Category: <strong>{{ $serviceRequest->category?->title }}</strong></p>
                </div>
                <button @click="dispatchModal = false" class="text-slate-400 hover:text-slate-800"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <!-- Instant Search Filter Bar -->
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400"></i>
                <input type="text" x-model="searchWorker" placeholder="Type name, worker code (SW-W-..), mobile number, or city..."
                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <form action="{{ route('service-requests.assign', $serviceRequest) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                    @forelse($suitableWorkers as $w)
                    <label x-show="!searchWorker || '{{ strtolower($w->name . ' ' . $w->worker_code . ' ' . $w->phone . ' ' . ($w->primaryCity?->name ?? '')) }}'.includes(searchWorker.toLowerCase())"
                           class="p-3.5 rounded-xl border border-slate-200 hover:border-emerald-500 flex items-center justify-between cursor-pointer transition bg-slate-50 hover:bg-emerald-50/50">
                        <div class="flex items-center space-x-3">
                            <input type="radio" name="worker_id" value="{{ $w->id }}" required class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="font-bold text-slate-900 text-sm">{{ $w->name }} <span class="font-mono font-normal text-slate-500 text-xs">({{ $w->worker_code }})</span></div>
                                <div class="text-slate-500 text-[11px]">{{ $w->phone }} &bull; {{ $w->primaryCity?->name }} &bull; {{ strtoupper($w->skill_level) }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold capitalize {{ $w->availability === 'available' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ str_replace('_', ' ', $w->availability) }}
                            </span>
                            <div class="text-[10px] text-slate-500 mt-1">{{ $w->active_jobs_count }} active jobs</div>
                        </div>
                    </label>
                    @empty
                    <div class="p-6 text-center text-slate-400">
                        No active workers matched for this category in {{ $serviceRequest->city?->name }}. You can register a worker or add this city to an existing worker's profile.
                    </div>
                    @endforelse
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="dispatchModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-xl">Cancel</button>
                    @if($suitableWorkers->count() > 0)
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm">Assign Selected Worker</button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div x-show="statusModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 text-xs border border-slate-200">
            <h3 class="text-base font-bold text-slate-900">Update Status to <span class="text-emerald-600 uppercase" x-text="targetStatus"></span></h3>
            <p class="text-slate-500">Record operational notes or reasons for this status update.</p>

            <form action="{{ route('service-requests.status', $serviceRequest) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" :value="targetStatus">

                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Operational Notes / Reason</label>
                    <textarea name="notes" rows="3" required
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none"
                              placeholder="Notes regarding this status change..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="statusModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm">Confirm Status Change</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Super Admin Technical Reversal & Delete Modal -->
    <div x-show="deleteModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 text-xs border border-slate-200">
            <div class="flex items-center space-x-3 text-rose-600">
                <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Delete Service Request?</h3>
                    <p class="text-[11px] text-slate-500 font-mono">{{ $serviceRequest->sr_number }}</p>
                </div>
            </div>

            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 space-y-1">
                <span class="font-bold block"><i class="fa-solid fa-shield-halved mr-1"></i> Financial Reversal Guarantee:</span>
                <p>Deleting this record will automatically and safely reverse any posted technician earnings or brand billing, cancel active assignments, and preserve ledger integrity.</p>
            </div>

            <form action="{{ route('service-requests.destroy', $serviceRequest) }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="deleteModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-sm">
                        <i class="fa-solid fa-trash-can mr-1"></i> Confirm & Safely Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Installation / Proof Photos Modal -->
    <div x-show="uploadProofModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 text-xs border border-slate-200" @click.away="uploadProofModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center space-x-2 text-emerald-600">
                    <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                    <h3 class="text-sm font-bold text-slate-900">Upload Installation / Proof Photos</h3>
                </div>
                <button type="button" @click="uploadProofModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('service-requests.proofs.upload', $serviceRequest) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="font-bold text-slate-800 block mb-1">Select Photo(s) <span class="text-rose-500">*</span></label>
                    <input type="file" name="images[]" multiple required accept="image/*"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <span class="text-[10px] text-slate-400 mt-1 block">You can select multiple photos (e.g. 4 photos) at once. They will automatically fill Picture 1, Picture 2, Picture 3... in the list.</span>
                </div>

                <div>
                    <label class="font-bold text-slate-800 block mb-1">Caption / Note (Optional)</label>
                    <input type="text" name="caption" placeholder="e.g. Work completed on site"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="uploadProofModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Upload Photos
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Image Lightbox Modal for Brand / Manager Inspection -->
    <div x-show="previewProofModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/90 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="max-w-5xl w-full space-y-3 bg-slate-900/90 p-4 sm:p-6 rounded-2xl border border-slate-700 shadow-2xl" @click.away="previewProofModal = false">
            <div class="flex items-center justify-between text-white text-xs border-b border-slate-700/80 pb-3">
                <div class="flex items-center space-x-2 truncate mr-3">
                    <i class="fa-solid fa-camera text-emerald-400"></i>
                    <span class="font-bold text-sm truncate" x-text="previewImgTitle"></span>
                </div>
                <div class="flex items-center space-x-3 shrink-0">
                    <a :href="previewImgSrc" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-lg text-xs font-semibold flex items-center space-x-1.5 transition border border-slate-600">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                        <span>Open Original File</span>
                    </a>
                    <button type="button" @click="previewProofModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg transition text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="bg-black/60 rounded-xl overflow-hidden max-h-[82vh] flex items-center justify-center p-2">
                <img :src="previewImgSrc" alt="Installation Proof Photo" class="max-h-[78vh] w-auto max-w-full object-contain rounded-lg shadow-lg">
            </div>
        </div>
    </div>

</div>
@endsection

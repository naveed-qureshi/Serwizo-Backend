@extends('layouts.app')

@section('title', 'Brand Installations - ' . $brand->name)
@section('header_title', $brand->name . ' Service Jobs')
@section('header_subtitle', 'Complete List of Free Installations, Barcode/Serials, Proof Photos & Durations')

@section('content')
<div class="space-y-6 pt-4" x-data="{ 
    showPicturesModal: false, 
    activeJobSr: '', 
    activeJobPhotos: [], 
    previewImage: null, 
    previewTitle: '',
    getProofLabel(type) {
        const labels = {
            'cat_indoor_serial': 'a. Indoor Serial Barcode',
            'cat_outdoor_serial': 'b. Outdoor Serial Barcode',
            'cat_warranty_card': 'c. Filled Warranty Card',
            'cat_indoor_unit': 'd. Indoor Unit Installed',
            'cat_outdoor_unit': 'e. Outdoor Unit Installed',
            'cat_indoor_box_serial': 'f. Indoor Box Serial',
            'cat_outdoor_box_serial': 'g. Outdoor Box Serial',
            'cat_leakage_test': 'h. Leakage Test Proof',
            'indoor_serial': 'a. Indoor Serial Barcode',
            'outdoor_serial': 'b. Outdoor Serial Barcode',
            'warranty_card': 'c. Filled Warranty Card',
            'indoor_unit': 'd. Indoor Unit Installed',
            'outdoor_unit': 'e. Outdoor Unit Installed',
            'indoor_box_serial': 'f. Indoor Box Serial',
            'outdoor_box_serial': 'g. Outdoor Box Serial',
            'leakage_test': 'h. Leakage Test Proof',
            'serial_barcode': 'Serial Number Barcode',
            'model_plate': 'Model Rating Plate',
            'completion_proof': 'Completion Proof',
            'general_proof': 'General Service Photo'
        };
        return labels[type] || (type ? type.replace(/_/g, ' ').toUpperCase() : 'Evidence Photo');
    }
}">

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('brand-portal.jobs') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search SR #, Customer, Contact #, Model, Serial #..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New / Unassigned</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned to Technician</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Pending QA)</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified &amp; Approved</option>
                </select>
            </div>

            <div>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-500" placeholder="From Date">
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg transition shadow-sm">Filter</button>
                @if(request()->hasAny(['search', 'status', 'start_date']))
                <a href="{{ route('brand-portal.jobs') }}" class="text-slate-500 hover:text-slate-800 text-xs whitespace-nowrap">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Jobs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                        <th class="py-3 px-4">Sr No.</th>
                        <th class="py-3 px-4">Date Start</th>
                        <th class="py-3 px-4">Date Completed</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Customer Contact</th>
                        <th class="py-3 px-4">Job Details</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4 text-center">Evidence</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jobs as $job)
                    <tr class="hover:bg-slate-50/80 transition">
                        <!-- Sr No. -->
                        <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                            <a href="{{ route('brand-portal.jobs.show', $job) }}" class="text-emerald-700 hover:text-emerald-900 font-mono hover:underline">
                                {{ $job->sr_number }}
                            </a>
                        </td>

                        <!-- Date Start -->
                        <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                            @if($job->started_at)
                                <div class="font-medium text-slate-800">{{ $job->started_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $job->started_at->format('H:i') }}</div>
                            @elseif($job->assigned_at)
                                <div class="font-medium text-slate-800">{{ $job->assigned_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $job->assigned_at->format('H:i') }} (Assigned)</div>
                            @else
                                <span class="text-slate-400">Not Started</span>
                            @endif
                        </td>

                        <!-- Date Completed -->
                        <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                            @if($job->completed_at)
                                <div class="font-medium text-slate-800">{{ $job->completed_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-emerald-600 font-mono font-semibold">{{ $job->completed_at->format('H:i') }} ({{ $job->formatted_duration }})</div>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">In Progress</span>
                            @endif
                        </td>

                        <!-- Customer Name -->
                        <td class="py-3.5 px-4 text-slate-900 font-bold">
                            {{ $job->customer?->name ?? 'Walk-in Customer' }}
                            @if($job->city)
                            <span class="block text-[11px] font-normal text-slate-400">{{ $job->city->name }}</span>
                            @endif
                        </td>

                        <!-- Customer Contact No. -->
                        <td class="py-3.5 px-4 text-slate-700 font-mono whitespace-nowrap">
                            @if($job->customer?->phone)
                            <a href="tel:{{ $job->customer->phone }}" class="hover:text-emerald-600 flex items-center space-x-1">
                                <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                                <span>{{ $job->customer->phone }}</span>
                            </a>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- Job Details -->
                        <td class="py-3.5 px-4 text-slate-700 max-w-xs">
                            <div class="font-semibold text-slate-900 truncate" title="{{ $job->serviceJob?->title ?? 'Brand Installation' }}">
                                {{ $job->serviceJob?->title ?? 'Brand Installation' }}
                            </div>
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5 space-x-2">
                                @if($job->model_number) <span>Model: <strong class="text-slate-700">{{ $job->model_number }}</strong></span> @endif
                                @if($job->serial_number) <span>S/N: <strong class="text-slate-700">{{ $job->serial_number }}</strong></span> @endif
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $job->status_badge_color }}">
                                {{ strtoupper(str_replace('_', ' ', $job->status)) }}
                            </span>
                        </td>

                        <!-- Amount (To be charged from brand) -->
                        <td class="py-3.5 px-4 font-black text-emerald-800 whitespace-nowrap">
                            Rs. {{ number_format($job->brand_rate ?: $job->customer_price, 2) }}
                        </td>

                        <!-- Pictures Button -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($job->proofs->count() > 0)
                            <button type="button" 
                                    @click="activeJobSr = '{{ $job->sr_number }}'; activeJobPhotos = {{ json_encode($job->proofs->map(fn($p) => ['url' => $p->image_url, 'type' => $p->proof_type, 'caption' => $p->caption, 'date' => $p->created_at?->format('M d, Y H:i')])) }}; showPicturesModal = true"
                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-lg text-xs font-bold transition shadow-sm cursor-pointer">
                                <i class="fa-solid fa-camera text-emerald-600"></i>
                                <span>Pictures ({{ $job->proofs->count() }})</span>
                            </button>
                            @else
                            <span class="inline-flex items-center space-x-1 px-2.5 py-1 text-[11px] text-slate-400 bg-slate-100 rounded-md font-medium">
                                <i class="fa-regular fa-image text-slate-300"></i>
                                <span>No Pictures</span>
                            </span>
                            @endif
                        </td>

                        <!-- Action -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('brand-portal.jobs.show', $job) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg font-semibold text-xs transition inline-flex items-center space-x-1">
                                <i class="fa-regular fa-eye"></i>
                                <span>View</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-screwdriver-wrench text-3xl mb-2 text-slate-300 block"></i>
                            <span class="font-medium text-slate-600">No service requests found matching your filters.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $jobs->links() }}
        </div>
        @endif
    </div>

    <!-- 1. Attached Pictures Evidence Gallery Modal -->
    <div x-show="showPicturesModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showPicturesModal = false">
        
        <div class="relative max-w-4xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="p-4 px-6 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold tracking-wide">Evidence &amp; Installation Pictures</h3>
                        <span class="text-xs text-slate-300 font-mono" x-text="'Job: ' + activeJobSr"></span>
                    </div>
                </div>
                <button @click="showPicturesModal = false" class="text-slate-400 hover:text-white transition p-1.5 rounded-lg hover:bg-slate-800">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Modal Body / Grid of Pictures -->
            <div class="p-6 overflow-y-auto flex-1 bg-slate-50">
                <p class="text-xs text-slate-500 mb-4">Click any picture below to view it in full screen high resolution.</p>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <template x-for="(pic, idx) in activeJobPhotos" :key="idx">
                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition cursor-pointer group"
                             @click="previewImage = pic.url; previewTitle = getProofLabel(pic.type)">
                            
                            <!-- Thumbnail with Hover Overlay -->
                            <div class="w-full h-36 bg-slate-200 overflow-hidden relative">
                                <img :src="pic.url" 
                                     :alt="getProofLabel(pic.type)" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-200" 
                                     loading="lazy"
                                     onerror="this.src='/images/logo.png'">
                                
                                <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold space-x-1.5">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                    <span>Full View</span>
                                </div>
                            </div>

                            <!-- Photo Label & Info -->
                            <div class="p-2.5 text-[11px] space-y-1">
                                <span class="font-bold text-slate-800 block truncate" x-text="getProofLabel(pic.type)"></span>
                                <template x-if="pic.caption">
                                    <p class="text-[10px] text-slate-500 truncate" x-text="pic.caption"></p>
                                </template>
                                <span class="text-[10px] text-slate-400 block font-mono" x-text="pic.date || ''"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-3 px-6 bg-white border-t border-slate-200 flex items-center justify-end">
                <button type="button" @click="showPicturesModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg text-xs transition">
                    Close Gallery
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Full-Screen Picture Lightbox Modal -->
    <div x-show="previewImage" 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.self="previewImage = null">
        
        <div class="relative max-w-4xl w-full bg-slate-900 p-3 rounded-2xl shadow-2xl flex flex-col items-center">
            <!-- Close Button -->
            <button @click="previewImage = null" class="absolute top-4 right-4 text-white bg-slate-800/90 rounded-full w-9 h-9 flex items-center justify-center hover:bg-rose-600 transition shadow-lg z-10">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>

            <!-- Picture Title -->
            <template x-if="previewTitle">
                <div class="w-full text-center py-2 text-white font-bold text-sm tracking-wide" x-text="previewTitle"></div>
            </template>

            <!-- Full Image -->
            <div class="w-full flex items-center justify-center overflow-hidden rounded-xl bg-slate-950 p-2">
                <img :src="previewImage" class="max-h-[80vh] w-auto max-w-full object-contain rounded-lg shadow-xl">
            </div>

            <!-- Download / Open in Tab Option -->
            <div class="mt-3 flex items-center space-x-3">
                <a :href="previewImage" target="_blank" download class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition inline-flex items-center space-x-1.5 shadow">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Open in New Tab</span>
                </a>
                <button @click="previewImage = null" class="px-4 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-lg transition">
                    Back to Gallery
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

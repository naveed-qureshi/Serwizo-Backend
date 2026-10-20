@extends('layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Operational Dashboard')
@section('header_subtitle', 'Live Service Dispatch, Worker Availability & Revenue Metrics')

@section('content')
<div class="space-y-6 pt-4">

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-2">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Time Range:</span>
            <div class="inline-flex rounded-lg shadow-sm border border-slate-200 bg-slate-50 p-0.5 text-xs font-medium">
                <a href="{{ route('dashboard', ['range' => 'today']) }}" 
                   class="px-3 py-1.5 rounded-md transition {{ $range === 'today' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">Today</a>
                <a href="{{ route('dashboard', ['range' => 'yesterday']) }}" 
                   class="px-3 py-1.5 rounded-md transition {{ $range === 'yesterday' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">Yesterday</a>
                <a href="{{ route('dashboard', ['range' => 'this_week']) }}" 
                   class="px-3 py-1.5 rounded-md transition {{ $range === 'this_week' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">This Week</a>
                <a href="{{ route('dashboard', ['range' => 'this_month']) }}" 
                   class="px-3 py-1.5 rounded-md transition {{ $range === 'this_month' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">This Month</a>
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center space-x-2 text-xs">
            <input type="hidden" name="range" value="custom">
            <input type="date" name="start_date" value="{{ request('start_date', $startDate->toDateString()) }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            <span class="text-slate-400">to</span>
            <input type="date" name="end_date" value="{{ request('end_date', $endDate->toDateString()) }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-medium transition">Filter</button>
        </form>
    </div>

    <!-- Top KPI Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Workers -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Workers</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fa-solid fa-user-gear"></i></span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ $totalWorkers }}</span>
                <span class="text-xs text-emerald-600 font-medium">({{ $activeWorkers }} Active)</span>
            </div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">
                <span class="text-emerald-600 font-bold">{{ $availableWorkers }}</span> available for dispatch
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Customers</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm"><i class="fa-solid fa-users"></i></span>
            </div>
            <div class="mt-2 flex items-baseline">
                <span class="text-2xl font-black text-slate-900">{{ $totalCustomers }}</span>
            </div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">
                Nationwide verified client database
            </div>
        </div>

        <!-- Total Jobs -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Jobs</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="fa-solid fa-headset"></i></span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ $totalPeriodRequests }}</span>
                <span class="text-xs text-amber-600 font-medium">({{ $pendingRequests }} Pending)</span>
            </div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">
                <span class="text-orange-600 font-bold">{{ $assignedRequests }}</span> assigned &bull; <span class="text-purple-600 font-bold">{{ $inProgressRequests }}</span> in progress
            </div>
        </div>

        <!-- Completed Jobs -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Completed / Verified</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="fa-solid fa-circle-check"></i></span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-emerald-600">{{ $completedPeriodRequests }}</span>
                @if($cancelledRequests > 0)
                <span class="text-xs text-rose-500 font-medium">({{ $cancelledRequests }} Cancelled)</span>
                @endif
            </div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">
                Successfully executed services
            </div>
        </div>
    </div>

    <!-- Financial Summary (Accounts & Super Admin) -->
    @if(auth()->user()->isAccounts())
    <div class="bg-gradient-to-r from-slate-900 via-navy-900 to-slate-950 p-6 rounded-2xl shadow-lg text-white">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-wallet text-emerald-400 text-lg"></i>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200">Financial Performance Overview</h3>
            </div>
            <span class="text-xs text-slate-400">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Customer Revenue</span>
                <div class="text-2xl font-black text-white mt-1">Rs. {{ number_format($periodRevenue, 2) }}</div>
                <span class="text-[11px] text-slate-400">Total billed for completed jobs</span>
            </div>

            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Worker Earnings Payable</span>
                <div class="text-2xl font-black text-blue-400 mt-1">Rs. {{ number_format($periodWorkerPayable, 2) }}</div>
                <span class="text-[11px] text-slate-400">Worker wages in period</span>
            </div>

            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">SerWizo Commission</span>
                <div class="text-2xl font-black text-emerald-400 mt-1">Rs. {{ number_format($periodCommission, 2) }}</div>
                <span class="text-[11px] text-emerald-300 font-medium">Net operating gross margin</span>
            </div>

            <div class="border-t sm:border-t-0 sm:border-l sm:pl-6 border-slate-800">
                <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider block">Total Outstanding Payable</span>
                <div class="text-2xl font-black text-amber-400 mt-1">Rs. {{ number_format($totalOutstandingPayable, 2) }}</div>
                <span class="text-[11px] text-slate-400">Cumulative worker ledger payables</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Operational Charts -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Status Breakdown Chart -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center justify-between">
                <span>Jobs by Status</span>
                <i class="fa-solid fa-chart-donut text-slate-400"></i>
            </h4>
            <div class="h-48 relative">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Category Breakdown Chart -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center justify-between">
                <span>Jobs by Category</span>
                <i class="fa-solid fa-chart-pie text-slate-400"></i>
            </h4>
            <div class="h-48 relative">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- City Breakdown Chart -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center justify-between">
                <span>Jobs by City</span>
                <i class="fa-solid fa-chart-bar text-slate-400"></i>
            </h4>
            <div class="h-48 relative">
                <canvas id="cityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Service Requests -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                    <span>Recent Service Requests</span>
                </h4>
                <a href="{{ route('service-requests.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentRequests as $sr)
                <div class="p-3.5 hover:bg-slate-50/80 transition flex items-center justify-between">
                    <div class="space-y-0.5">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('service-requests.show', $sr) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                {{ $sr->sr_number }}
                            </a>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $sr->status_badge_color }}">
                                {{ strtoupper(str_replace('_', ' ', $sr->status)) }}
                            </span>
                        </div>
                        <p class="text-slate-600 truncate max-w-xs">{{ $sr->customer?->name }} &bull; {{ $sr->serviceJob?->title }}</p>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-slate-900">Rs. {{ number_format($sr->customer_price, 2) }}</div>
                        <div class="text-[10px] text-slate-400">{{ $sr->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400">No service requests logged yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Completed Jobs -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>Recently Completed Jobs</span>
                </h4>
                <a href="{{ route('service-requests.index', ['status' => 'completed']) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentCompletedJobs as $job)
                <div class="p-3.5 hover:bg-slate-50/80 transition flex items-center justify-between">
                    <div class="space-y-0.5">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('service-requests.show', $job) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                {{ $job->sr_number }}
                            </a>
                            <span class="text-slate-500">&bull; {{ $job->currentWorker?->name ?? 'Unassigned' }}</span>
                        </div>
                        <p class="text-slate-600 truncate max-w-xs">{{ $job->serviceJob?->title }} ({{ $job->city?->name }})</p>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-emerald-600">Rs. {{ number_format($job->customer_price, 2) }}</div>
                        <div class="text-[10px] text-slate-400">Wage: Rs. {{ number_format($job->worker_payment, 2) }}</div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400">No completed jobs yet.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Status Chart
        const statusData = @json($statusCounts);
        const statusKeys = Object.keys(statusData);
        const statusValues = Object.values(statusData);
        
        const statusColors = {
            'new': '#3b82f6',
            'pending_assignment': '#f59e0b',
            'assigned': '#f97316',
            'accepted': '#6366f1',
            'on_the_way': '#06b6d4',
            'arrived': '#14b8a6',
            'in_progress': '#a855f7',
            'completed': '#10b981',
            'verified': '#059669',
            'cancelled': '#ef4444'
        };

        const bgColors = statusKeys.map(k => statusColors[k] || '#94a3b8');

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const chart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusKeys.map(s => s.replace(/_/g, ' ').toUpperCase()),
                    datasets: [{
                        data: statusValues,
                        backgroundColor: bgColors,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: (evt, activeElements) => {
                        if (activeElements.length > 0) {
                            const index = activeElements[0].index;
                            const status = statusKeys[index];
                            window.location.href = `{{ route('service-requests.index') }}?status=${status}`;
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { boxWidth: 10, font: { size: 10 } }
                        }
                    }
                }
            });
        }

        // Category Chart
        const catData = @json($categoryCounts);
        const catCtx = document.getElementById('categoryChart');
        if (catCtx) {
            new Chart(catCtx, {
                type: 'pie',
                data: {
                    labels: catData.map(c => c.name),
                    datasets: [{
                        data: catData.map(c => c.count),
                        backgroundColor: ['#10b981', '#3b82f6', '#f97316', '#8b5cf6', '#ec4899', '#eab308', '#06b6d4', '#84cc16']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: (evt, activeElements) => {
                        if (activeElements.length > 0) {
                            const index = activeElements[0].index;
                            const cat = catData[index];
                            if (cat && cat.id) {
                                window.location.href = `{{ route('service-requests.index') }}?category_id=${cat.id}`;
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } }
                    }
                }
            });
        }

        // City Chart
        const cityData = @json($cityCounts);
        const cityCtx = document.getElementById('cityChart');
        if (cityCtx) {
            new Chart(cityCtx, {
                type: 'bar',
                data: {
                    labels: cityData.map(c => c.name),
                    datasets: [{
                        label: 'Jobs',
                        data: cityData.map(c => c.count),
                        backgroundColor: '#0284c7',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: (evt, activeElements) => {
                        if (activeElements.length > 0) {
                            const index = activeElements[0].index;
                            const city = cityData[index];
                            if (city && city.id) {
                                window.location.href = `{{ route('service-requests.index') }}?city_id=${city.id}`;
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                        x: { ticks: { font: { size: 10 } } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }
    });
</script>
@endsection

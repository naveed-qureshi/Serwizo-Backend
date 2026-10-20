<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - SerWizo Service Management</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        },
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false }">

    <div class="min-h-full flex flex-col lg:flex-row">
        
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 bg-slate-900/80 z-40 lg:hidden" x-cloak></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-navy-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shrink-0">
            
            <!-- Brand Header -->
            <div class="h-16 flex items-center justify-between px-6 bg-navy-950 border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-emerald-500/30">
                        S
                    </div>
                    <div>
                        <span class="text-lg font-bold text-white tracking-wide">SerWizo</span>
                        <span class="block text-[10px] text-emerald-400 font-medium uppercase tracking-wider">Service Management</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1.5 text-sm">
                
                @if(auth()->user()->isBrand())
                <div class="px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Brand Portal</div>
                
                <a href="{{ route('brand-portal.dashboard') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('brand-portal.dashboard') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Brand Overview</span>
                </a>

                <a href="{{ route('brand-portal.jobs') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('brand-portal.jobs*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-screwdriver-wrench w-5 text-center"></i>
                    <span>Installation Jobs</span>
                </a>

                <a href="{{ route('brand-portal.ledger') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('brand-portal.ledger*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i>
                    <span>Ledger & Billing</span>
                </a>
                @else

                <div class="px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Core</div>
                
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                @if(auth()->user()->isOperations())
                <a href="{{ route('service-requests.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('service-requests.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-headset w-5 text-center"></i>
                    <span>Service Requests</span>
                </a>

                <a href="{{ route('workers.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('workers.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-user-gear w-5 text-center"></i>
                    <span>Workers</span>
                </a>

                <a href="{{ route('customers.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('customers.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span>Customers</span>
                </a>

                <div class="pt-4 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Catalog & Brands</div>

                <a href="{{ route('brands.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('brands.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-building w-5 text-center"></i>
                    <span>Brands</span>
                </a>

                <a href="{{ route('jobs.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('jobs.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-briefcase w-5 text-center"></i>
                    <span>Job Services</span>
                </a>

                <a href="{{ route('categories.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('categories.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-tags w-5 text-center"></i>
                    <span>Categories</span>
                </a>

                <a href="{{ route('sub-categories.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('sub-categories.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-layer-group w-5 text-center"></i>
                    <span>Sub-Categories</span>
                </a>

                <a href="{{ route('cities.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('cities.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-city w-5 text-center"></i>
                    <span>Cities</span>
                </a>
                @endif

                @if(auth()->user()->isAccounts())
                <div class="pt-4 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Finance & Ledgers</div>

                <a href="{{ route('brand-ledger.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('brand-ledger.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i>
                    <span>Brand Ledgers</span>
                </a>

                <a href="{{ route('ledger.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('ledger.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-book-journal-whills w-5 text-center"></i>
                    <span>Worker Ledgers</span>
                </a>

                <a href="{{ route('payments.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('payments.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-money-bill-wave w-5 text-center"></i>
                    <span>Worker Payments</span>
                </a>
                @endif

                <div class="pt-4 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Reports</div>

                @if(auth()->user()->isAccounts())
                <a href="{{ route('reports.revenue') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('reports.revenue') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span>Revenue Report</span>
                </a>
                @endif

                @if(auth()->user()->isOperations())
                <a href="{{ route('reports.jobs') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('reports.jobs') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-list-check w-5 text-center"></i>
                    <span>Job Operations Report</span>
                </a>
                @endif

                @if(auth()->user()->isSuperAdmin())
                <div class="pt-4 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Administration</div>

                <a href="{{ route('users.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-user-shield w-5 text-center"></i>
                    <span>Staff & Roles</span>
                </a>

                <a href="{{ route('audit-logs.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('audit-logs.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
                    <span>Audit Trail</span>
                </a>

                <a href="{{ route('backups.index') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('backups.*') ? 'bg-emerald-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fa-solid fa-database w-5 text-center"></i>
                    <span>Database Backups</span>
                </a>
                @endif
                @endif

            </nav>

            <!-- User Footer in Sidebar -->
            <div class="p-4 bg-navy-950 border-t border-slate-800 flex items-center justify-between text-xs">
                <div class="flex items-center space-x-2.5 truncate">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center font-bold text-white text-sm">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="truncate">
                        <div class="font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-slate-400 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign Out" class="text-slate-400 hover:text-rose-400 transition p-1.5 rounded-md hover:bg-slate-800">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Top Navigation Bar -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30 shadow-sm">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-600 hover:text-slate-900 focus:outline-none">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 leading-none">@yield('header_title', 'Dashboard')</h1>
                        <p class="text-xs text-slate-500 mt-0.5">@yield('header_subtitle', 'SerWizo Nationwide Operations Portal')</p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    @if(auth()->user()->isOperations())
                    <a href="{{ route('service-requests.create') }}" 
                       class="inline-flex items-center space-x-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        <i class="fa-solid fa-plus"></i>
                        <span class="hidden sm:inline">New Service Request</span>
                    </a>
                    @endif

                    <a href="{{ route('profile') }}" class="p-2 text-slate-500 hover:text-slate-800 transition rounded-lg hover:bg-slate-100">
                        <i class="fa-regular fa-circle-user text-lg"></i>
                    </a>
                </div>
            </header>

            <!-- Notification Toast Messages -->
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                @if(session('success'))
                <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
                </div>
                @endif

                @if($errors->any())
                <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-2.5">
                            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base mt-0.5"></i>
                            <div>
                                <div class="font-semibold mb-1">Please review the following issues:</div>
                                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                                    @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 px-4 sm:px-6 lg:px-8 pb-10">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 py-3 px-6 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} SerWizo (Pvt) Ltd. All rights reserved. Service Management Platform.
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="en" x-data="dashboardApp()" x-init="init()">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Admin Panel</title>
    <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">

    <!-- Tailwind CSS & JS Bundle -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .font-georgia { 
            font-family: Georgia, 'Times New Roman', Times, serif; 
        }
        
        .font-sans {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
        
        body {
            font-family: Georgia, 'Times New Roman', Times, serif;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 50%, #e5e7eb 100%);
        }
        
        .sidebar-glass {
            background: rgba(27, 60, 83, 0.98);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 8px 0 32px rgba(0, 0, 0, 0.1);
        }
        
        .header-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .stat-card {
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .nav-link {
            border-radius: 12px;
            padding: 12px 16px;
            margin: 4px 0;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #F59E0B;
            font-weight: 600;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.9);
        }
        
        /* Content text should use sans-serif */
        .content-text {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
        
        /* Numbers in stat cards should use sans-serif */
        .stat-number {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            font-weight: 700;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 sidebar-glass text-white flex flex-col fixed h-full z-40">
            <!-- Top Section -->
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3 mb-4">
                    <img src="{{ asset('img/mainlogo.png') }}" alt="City Hall Logo" class="w-10 h-10 object-contain" />
                    <h1 class="text-xl font-georgia font-bold">
                        @if(session('role') === 'admin')
                            NCC Admin
                        @else
                            NCC Staff
                        @endif
                    </h1>
                </div>
                <div class="text-sm text-gray-300">
                    <p class="font-medium">Welcome, {{ session('username') }}</p>
                    <span class="inline-block text-xs bg-amber-600 px-3 py-1 rounded-full mt-2 font-medium">
                        {{ ucfirst(str_replace('_', ' ', session('role'))) }}
                    </span>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard-main') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.dashboard-main') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </div>
                </a>
                
                <!-- User Management (Admin Only) -->
                @if(session('role') === 'admin')
                <a href="{{ route('admin.usermanagement') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.usermanagement') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </div>
                </a>
                @endif
                
                <!-- Queue Status -->
                <a href="{{ route('admin.queuestatus') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.queuestatus') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-list"></i>
                        <span>Queue Status</span>
                    </div>
                </a>
                
                <!-- Mailbox -->
                <a href="{{ route('admin.mailbox') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.mailbox') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-envelope"></i>
                        <span>Mailbox</span>
                    </div>
                </a>
            </nav>
            
            <!-- Logout -->
            <div class="px-4 py-4 border-t border-white/10">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-xl font-georgia font-semibold transition">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </div>
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 flex flex-col ml-64">
            <!-- Header -->
            <header class="header-glass sticky top-0 z-30 px-8 py-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('img/philogo.png') }}" alt="PH Logo" class="w-12 h-12 object-contain drop-shadow-lg" />
                        <div>
                            <h1 class="text-2xl font-georgia font-bold text-gray-900">North Caloocan City Hall</h1>
                            <p class="text-sm text-gray-600">Administrative Dashboard</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-georgia font-semibold text-gray-900">{{ session('username') }}</p>
                        <p x-text="currentDateTime" class="text-xs text-gray-500"></p>
                    </div>
                </div>
            </header>

            <!-- Dashboard Overview -->
            <section class="px-8 py-6">
                <div>
                    <h2 class="text-3xl font-georgia font-bold text-gray-900 mb-2">Dashboard Overview</h2>
                    <p class="text-gray-600 mb-8 content-text" x-text="`Real-time statistics for ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`"></p>
                </div>
                
                <!-- Stats Grid - Now with 4 cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Pre-Registered -->
                    <div class="stat-card rounded-3xl shadow-xl p-10 hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex flex-col items-center text-center space-y-6">
                            <div class="w-24 h-24 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <img src="{{ asset('img/pending.png') }}" alt="Pre-Registered" class="w-12 h-12 brightness-0 invert" />
                            </div>
                            <div class="w-full">
                                <h3 class="text-5xl stat-number text-yellow-600 mb-3" x-text="stats.pre_registered || 0"></h3>
                                <p class="text-base font-semibold text-gray-700 content-text">Pre-Registered</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cancelled Today -->
                    <div class="stat-card rounded-3xl shadow-xl p-10 hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex flex-col items-center text-center space-y-6">
                            <div class="w-24 h-24 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <img src="{{ asset('img/cancelled.png') }}" alt="Cancelled" class="w-12 h-12 brightness-0 invert" />
                            </div>
                            <div class="w-full">
                                <h3 class="text-5xl stat-number text-red-600 mb-3" x-text="stats.cancelled || 0"></h3>
                                <p class="text-base font-semibold text-gray-700 content-text">Cancelled Today</p>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Today -->
                    <div class="stat-card rounded-3xl shadow-xl p-10 hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex flex-col items-center text-center space-y-6">
                            <div class="w-24 h-24 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <img src="{{ asset('img/complete.png') }}" alt="Completed" class="w-12 h-12 brightness-0 invert" />
                            </div>
                            <div class="w-full">
                                <h3 class="text-5xl stat-number text-green-600 mb-3" x-text="stats.completed || 0"></h3>
                                <p class="text-base font-semibold text-gray-700 content-text">Completed Today</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mailbox Messages -->
                    <div class="stat-card rounded-3xl shadow-xl p-10 hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex flex-col items-center text-center space-y-6">
                            <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="w-full">
                                <h3 class="text-5xl stat-number text-purple-600 mb-3" x-text="stats.mailbox_messages || 0"></h3>
                                <p class="text-base font-semibold text-gray-700 content-text">Mailbox Messages</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Footer -->
    <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-20 text-center">
        <p class="text-white text-sm opacity-80 drop-shadow">© 2025 North Caloocan City Hall Assessment Department</p>
    </div>

    <script>
        function dashboardApp() {
            return {
                stats: @json($stats ?? []),
                currentDateTime: '',

                init() {
                    this.updateDateTime();
                    
                    // Start auto-refresh for stats
                    setTimeout(() => {
                        this.startStatsRefresh();
                    }, 2000);

                    // Update time every second
                    setInterval(() => {
                        this.updateDateTime();
                    }, 1000);
                },

                updateDateTime() {
                    const now = new Date();
                    this.currentDateTime = now.toLocaleString("en-US", {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                },

                async refreshStatsOnly() {
                    try {
                        const response = await fetch('/admin/dashboard-stats', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        
                        const data = await response.json();
                        console.log('Stats updated:', data);

                        // Update the stats with new field names
                        this.stats = {
                            pre_registered: data.pre_registered || 0,
                            cancelled: data.cancelled || 0,
                            completed: data.completed || 0,
                            mailbox_messages: data.mailbox_messages || 0
                        };

                    } catch (error) {
                        console.error('Failed to refresh stats:', error);
                    }
                },

                startStatsRefresh() {
                    this.refreshStatsOnly();
                    setInterval(() => {
                        this.refreshStatsOnly();
                    }, 15000);
                }
            };
        }
    </script>
</body>
</html>
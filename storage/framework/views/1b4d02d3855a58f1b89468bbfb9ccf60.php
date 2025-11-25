<!DOCTYPE html>
<html lang="en" x-data="dashboardApp()" x-init="init()">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Admin Panel</title>
    <link rel="icon" href="<?php echo e(asset('img/mainlogo.png')); ?>" type="image/png">

    <!-- Tailwind CSS & JS Bundle -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

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
                    <img src="<?php echo e(asset('img/mainlogo.png')); ?>" alt="City Hall Logo" class="w-10 h-10 object-contain" />
                    <h1 class="text-xl font-georgia font-bold">
                        <?php if(session('role') === 'admin'): ?>
                            NCC Admin
                        <?php else: ?>
                            NCC Staff
                        <?php endif; ?>
                    </h1>
                </div>
                <div class="text-sm text-gray-300">
                    <p class="font-medium">Welcome, <?php echo e(session('username')); ?></p>
                    <span class="inline-block text-xs bg-amber-600 px-3 py-1 rounded-full mt-2 font-medium">
                        <?php echo e(ucfirst(str_replace('_', ' ', session('role')))); ?>

                    </span>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <!-- Dashboard -->
                <a href="<?php echo e(route('admin.dashboard-main')); ?>" 
                   class="nav-link block font-georgia text-white <?php echo e(request()->routeIs('admin.dashboard-main') ? 'active' : ''); ?>">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </div>
                </a>
                
                <!-- User Management (Admin Only) -->
                <?php if(session('role') === 'admin'): ?>
                <a href="<?php echo e(route('admin.usermanagement')); ?>" 
                   class="nav-link block font-georgia text-white <?php echo e(request()->routeIs('admin.usermanagement') ? 'active' : ''); ?>">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </div>
                </a>
                <?php endif; ?>
                
                <!-- Queue Status -->
                <a href="<?php echo e(route('admin.queuestatus')); ?>" 
                   class="nav-link block font-georgia text-white <?php echo e(request()->routeIs('admin.queuestatus') ? 'active' : ''); ?>">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-list"></i>
                        <span>Queue Status</span>
                    </div>
                </a>

                <!-- Pre-Registrations -->
                <a href="<?php echo e(route('admin.preregs')); ?>" 
                   class="nav-link block font-georgia text-white <?php echo e(request()->routeIs('admin.preregs') ? 'active' : ''); ?>">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-file-alt"></i>
                        <span>Pre-Registrations</span>
                    </div>
                </a>
            </nav>
            
            <!-- Logout -->
            <div class="px-4 py-4 border-t border-white/10">
                <form action="<?php echo e(route('admin.logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
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
                        <img src="<?php echo e(asset('img/philogo.png')); ?>" alt="PH Logo" class="w-12 h-12 object-contain drop-shadow-lg" />
                        <div>
                            <h1 class="text-2xl font-georgia font-bold text-gray-900">North Caloocan City Hall</h1>
                            <p class="text-sm text-gray-600">Administrative Dashboard</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-georgia font-semibold text-gray-900"><?php echo e(session('username')); ?></p>
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
                
                <!-- Stats Grid - Now with 4 distinct cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Pending Review (Step 1) -->
                    <div class="stat-card rounded-3xl shadow-xl p-8 hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex flex-col items-center text-center space-y-4">
                            <div class="w-20 h-20 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-clock text-white text-3xl"></i>
                            </div>
                            <div class="w-full">
                                <h3 class="text-4xl stat-number text-orange-600 mb-2" x-text="stats.pending_review || 0"></h3>
                                <p class="text-sm font-semibold text-gray-700 content-text">Pending Review</p>
                                <p class="text-xs text-gray-500 content-text mt-1">Awaiting approval</p>
                            </div>
                        </div>
                    </div>

                    <!-- Approved & Waiting (Step 2) -->
                    <div class="stat-card rounded-3xl shadow-xl p-8 hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex flex-col items-center text-center space-y-4">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-check-circle text-white text-3xl"></i>
                            </div>
                            <div class="w-full">
                                <h3 class="text-4xl stat-number text-blue-600 mb-2" x-text="stats.approved_waiting || 0"></h3>
                                <p class="text-sm font-semibold text-gray-700 content-text">Approved & Waiting</p>
                                <p class="text-xs text-gray-500 content-text mt-1">Ready to enter queue</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cancelled Today -->
                    <div class="stat-card rounded-3xl shadow-xl p-8 hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex flex-col items-center text-center space-y-4">
                            <div class="w-20 h-20 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-times-circle text-white text-3xl"></i>
                            </div>
                            <div class="w-full">
                                <h3 class="text-4xl stat-number text-red-600 mb-2" x-text="stats.cancelled || 0"></h3>
                                <p class="text-sm font-semibold text-gray-700 content-text">Cancelled Today</p>
                                <p class="text-xs text-gray-500 content-text mt-1">Queue cancellations</p>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Today -->
                    <div class="stat-card rounded-3xl shadow-xl p-8 hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex flex-col items-center text-center space-y-4">
                            <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-check-double text-white text-3xl"></i>
                            </div>
                            <div class="w-full">
                                <h3 class="text-4xl stat-number text-green-600 mb-2" x-text="stats.completed || 0"></h3>
                                <p class="text-sm font-semibold text-gray-700 content-text">Completed Today</p>
                                <p class="text-xs text-gray-500 content-text mt-1">Successfully served</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats Grid -->
                <div class="mt-12">
                    <h3 class="text-2xl font-georgia font-bold text-gray-900 mb-6">Queue Activity</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Currently Waiting in Queue -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-users text-purple-600 text-xl"></i>
                                </div>
                                <span class="text-3xl stat-number text-purple-600" x-text="stats.waiting || 0"></span>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 content-text">Waiting in Queue</p>
                            <p class="text-xs text-gray-500 content-text mt-1">Active queue</p>
                        </div>

                        <!-- Currently Being Served -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-hand-holding text-indigo-600 text-xl"></i>
                                </div>
                                <span class="text-3xl stat-number text-indigo-600" x-text="stats.serving || 0"></span>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 content-text">Now Serving</p>
                            <p class="text-xs text-gray-500 content-text mt-1">Being processed</p>
                        </div>

                        <!-- Total Entries Today -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-calendar-day text-cyan-600 text-xl"></i>
                                </div>
                                <span class="text-3xl stat-number text-cyan-600" x-text="stats.total_today || 0"></span>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 content-text">Total Today</p>
                            <p class="text-xs text-gray-500 content-text mt-1">All entries</p>
                        </div>

                        <!-- Requeued -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-redo text-amber-600 text-xl"></i>
                                </div>
                                <span class="text-3xl stat-number text-amber-600" x-text="stats.requeued || 0"></span>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 content-text">Requeued</p>
                            <p class="text-xs text-gray-500 content-text mt-1">Awaiting recall</p>
                        </div>
                    </div>
                </div>

                <!-- Priority & Entry Type Breakdown -->
                <div class="mt-12">
                    <h3 class="text-2xl font-georgia font-bold text-gray-900 mb-6">Queue Breakdown</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Priority Types -->
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 content-text flex items-center">
                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                Priority Status
                            </h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-wheelchair text-green-600 mr-3"></i>
                                        <span class="font-medium text-gray-700 content-text">PWD</span>
                                    </div>
                                    <span class="text-2xl stat-number text-green-600" x-text="stats.pwd_waiting || 0"></span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-clock text-blue-600 mr-3"></i>
                                        <span class="font-medium text-gray-700 content-text">Senior Citizen</span>
                                    </div>
                                    <span class="text-2xl stat-number text-blue-600" x-text="stats.senior_waiting || 0"></span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-gray-600 mr-3"></i>
                                        <span class="font-medium text-gray-700 content-text">Regular</span>
                                    </div>
                                    <span class="text-2xl stat-number text-gray-600" x-text="stats.regular_waiting || 0"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Entry Types -->
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 content-text flex items-center">
                                <i class="fas fa-door-open text-indigo-500 mr-2"></i>
                                Entry Method
                            </h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-indigo-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-desktop text-indigo-600 mr-3"></i>
                                        <span class="font-medium text-gray-700 content-text">Kiosk (Direct)</span>
                                    </div>
                                    <span class="text-2xl stat-number text-indigo-600" x-text="stats.kiosk_waiting || 0"></span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-import text-purple-600 mr-3"></i>
                                        <span class="font-medium text-gray-700 content-text">Pre-Registration</span>
                                    </div>
                                    <span class="text-2xl stat-number text-purple-600" x-text="stats.prereg_waiting || 0"></span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-chart-line text-emerald-600 mr-3"></i>
                                        <span class="font-medium text-gray-700 content-text">Total Waiting</span>
                                    </div>
                                    <span class="text-2xl stat-number text-emerald-600" x-text="(stats.kiosk_waiting || 0) + (stats.prereg_waiting || 0)"></span>
                                </div>
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
                stats: <?php echo json_encode($stats ?? [], 15, 512) ?>,
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

                        // Updated to use all new stat fields
                        this.stats = {
                            pending_review: data.pending_review || 0,
                            approved_waiting: data.approved_waiting || 0,
                            cancelled: data.cancelled || 0,
                            completed: data.completed || 0,
                            waiting: data.waiting || 0,
                            serving: data.serving || 0,
                            total_today: data.total_today || 0,
                            requeued: data.requeued || 0,
                            pwd_waiting: data.pwd_waiting || 0,
                            senior_waiting: data.senior_waiting || 0,
                            regular_waiting: data.regular_waiting || 0,
                            kiosk_waiting: data.kiosk_waiting || 0,
                            prereg_waiting: data.prereg_waiting || 0
                        };

                    } catch (error) {
                        console.error('Failed to refresh stats:', error);
                    }
                },

                startStatsRefresh() {
                    this.refreshStatsOnly();
                    setInterval(() => {
                        this.refreshStatsOnly();
                    }, 15000); // Refresh every 15 seconds
                }
            };
        }
    </script>
</body>
</html><?php /**PATH C:\Users\kiosk\Documents\thesis\resources\views/Admin/dashboard.blade.php ENDPATH**/ ?>
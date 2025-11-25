<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Queue Status - Admin Panel</title>
    <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .font-georgia { font-family: Georgia, 'Times New Roman', Times, serif; }
        body { font-family: Georgia, 'Times New Roman', Times, serif; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 50%, #e5e7eb 100%); }
        .sidebar-glass { background: rgba(27, 60, 83, 0.98); backdrop-filter: blur(20px); border-right: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 8px 0 32px rgba(0, 0, 0, 0.1); }
        .header-glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); }
        .nav-link { border-radius: 12px; padding: 12px 16px; margin: 4px 0; }
        .nav-link:hover { background: rgba(255, 255, 255, 0.1); }
        .nav-link.active { background: rgba(255, 255, 255, 0.15); border-left: 4px solid #F59E0B; font-weight: 600; }
        .queue-card { transition: all 0.3s ease; }
        .queue-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .content-text { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; }
        .modal-overlay { background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(2px); }
        .btn-disabled { opacity: 0.5; cursor: not-allowed; }
        [x-cloak] { display: none !important; }
        
        /* Entry type badges */
        .badge-prereg { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .badge-kiosk { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        
        /* Filter button styles */
        .filter-btn { transition: all 0.2s ease; border: 2px solid transparent; }
        .filter-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .filter-btn.active { border-color: currentColor; font-weight: 600; }
        
        /* Row highlighting by entry type */
        .row-prereg { border-left: 3px solid #3b82f6; }
        .row-kiosk { border-left: 3px solid #8b5cf6; }
        
        /* Requeued row style */
        .row-requeued { border-left: 3px solid #f97316; }
        
        /* Image zoom styles */
        .cursor-zoom-in { cursor: zoom-in; }
        .cursor-zoom-in:hover img { transform: scale(1.05); transition: transform 0.2s; }
        
        /* Now Serving Compact Card */
        .now-serving-compact { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
    </style>

    <script>
window.queueAppData = function () {
    return {
        currentDateTime: '',
        pendingAction: null,
        pendingActionName: '',
        nowServingActive: false,
        nowServingData: null,
        isProcessing: false,
        showModal: false,
        showDetailsModal: false,
        currentFilter: 'all',
        lastFetchError: null,
        counts: {
            priority_total: 0,
            priority_prereg: 0,
            priority_kiosk: 0,
            regular_total: 0,
            regular_prereg: 0,
            regular_kiosk: 0,
            requeued_total: 0
        },

        init() {
            this.updateDateTime();
            setInterval(() => this.updateDateTime(), 1000);
            this.fetchQueueData();
            setInterval(() => this.fetchQueueData(), 5000);
        },

        updateDateTime() {
            const now = new Date();
            this.currentDateTime = now.toLocaleString("en-US", {
                weekday: 'short', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true
            });
            document.getElementById('currentDateTime').textContent = this.currentDateTime;
        },

        setFilter(filter) {
            this.currentFilter = filter;
            this.fetchQueueData();
        },

        getEntryTypeBadge(entryType) {
            if (entryType === 'pre_registration') {
                return '<span class="px-2 py-1 rounded-full text-xs text-white badge-prereg inline-flex items-center gap-1"><i class="fas fa-qrcode"></i> Pre-Reg</span>';
            } else {
                return '<span class="px-2 py-1 rounded-full text-xs text-white badge-kiosk inline-flex items-center gap-1"><i class="fas fa-desktop"></i> Kiosk</span>';
            }
        },

        getEntryTypeText(entryType) {
            return entryType === 'pre_registration' ? 'Pre-Registration (QR)' : 'Walk-in (Kiosk)';
        },

        getRowClass(type, entryType) {
            if (type === 'requeued') return 'row-requeued';
            return entryType === 'pre_registration' ? 'row-prereg' : 'row-kiosk';
        },

        openDetailsModal() {
            if (!this.nowServingActive || !this.nowServingData) {
                this.showNotification('No one is currently being served', 'error');
                return;
            }
            this.showDetailsModal = true;
        },

        closeDetailsModal() {
            this.showDetailsModal = false;
        },

        openImageZoom(imageSrc) {
            const modal = document.getElementById('zoomModal');
            const img = document.getElementById('zoomedImage');
            
            if (window.panzoomInstance) {
                window.panzoomInstance.dispose();
                window.panzoomInstance = null;
            }
            
            img.style.transform = 'scale(1) translate(0, 0)';
            img.src = '';
            img.classList.remove('opacity-100');
            img.classList.add('opacity-0');
            modal.classList.remove('hidden');
            
            img.onload = function () {
                img.classList.remove('opacity-0');
                img.classList.add('opacity-100');
                
                window.panzoomInstance = Panzoom(document.getElementById('zoomContainer'), {
                    contain: 'outside',
                    maxScale: 5,
                    minScale: 0.3,
                    zoomSpeed: 0.8,
                    panOnlyWhenZoomed: true,
                    cursor: 'move'
                });
            };
            
            img.src = imageSrc;
        },

        async fetchQueueData() {
            try {
                const url = `/admin/queue?entry_type=${this.currentFilter}`;
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }

                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Server returned HTML instead of JSON. Possible session expiry.');
                    return;
                }

                const data = await res.json();
                this.counts = data.counts;

                const nowServingEl = document.getElementById('nowServing');
                if (data.now_serving) {
                    this.nowServingData = data.now_serving;
                    const entryBadge = this.getEntryTypeBadge(data.now_serving.entry_type);
                    nowServingEl.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-2xl font-bold">Queue #${data.now_serving.queue_number}</span>
                                <span class="mx-2">-</span>
                                <span class="text-lg">${data.now_serving.full_name}</span>
                                <span class="ml-2">${entryBadge}</span>
                            </div>
                            <button @click="openDetailsModal()" class="px-4 py-2 bg-white hover:bg-blue-50 text-blue-600 rounded-lg font-medium transition flex items-center gap-2 shadow-sm">
                                <i class="fas fa-eye"></i>
                                View Details
                            </button>
                        </div>
                    `;
                    this.nowServingActive = true;
                } else {
                    this.nowServingData = null;
                    nowServingEl.innerHTML = '<div class="text-center py-2 text-gray-400">No one being served</div>';
                    this.nowServingActive = false;
                }

                const priorityBody = document.getElementById("priorityQueueBody");
                if (priorityBody) {
                    priorityBody.innerHTML = data.priority.length > 0 ?
                        data.priority.map(p => `
                            <tr class="queue-card hover:bg-gray-50 ${this.getRowClass(null, p.entry_type)}">
                                <td class="px-6 py-4">${p.queue_number}</td>
                                <td class="px-6 py-4">${p.name}</td>
                                <td class="px-6 py-4">${p.service_type}</td>
                                <td class="px-6 py-4">${this.getEntryTypeBadge(p.entry_type)}</td>
                            </tr>
                        `).join('') :
                        '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No one in priority queue</td></tr>';
                }

                const regularBody = document.getElementById("regularQueueBody");
                if (regularBody) {
                    regularBody.innerHTML = data.regular.length > 0 ?
                        data.regular.map(r => `
                            <tr class="queue-card hover:bg-gray-50 ${this.getRowClass(null, r.entry_type)}">
                                <td class="px-6 py-4">${r.queue_number}</td>
                                <td class="px-6 py-4">${r.name}</td>
                                <td class="px-6 py-4">${r.service_type}</td>
                                <td class="px-6 py-4">${this.getEntryTypeBadge(r.entry_type)}</td>
                            </tr>
                        `).join('') :
                        '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No one in regular queue</td></tr>';
                }

                const requeuedBody = document.getElementById("requeuedQueueBody");
                if (requeuedBody) {
                    requeuedBody.innerHTML = data.requeued.length > 0 ?
                        data.requeued.map(r => `
                            <tr class="queue-card hover:bg-gray-50 ${this.getRowClass('requeued', r.entry_type)}">
                                <td class="px-6 py-4 font-semibold text-orange-600">${r.queue_number}</td>
                                <td class="px-6 py-4">${r.name}</td>
                                <td class="px-6 py-4">${r.service_type}</td>
                                <td class="px-6 py-4">
                                    <button 
                                        @click="recallFromRequeue(${r.id}, '${r.queue_number}')" 
                                        class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded inline-flex items-center gap-1 transition"
                                        title="Recall this client now">
                                        <i class="fas fa-redo-alt"></i> Recall Now
                                    </button>
                                </td>
                            </tr>
                        `).join('') :
                        '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No requeued clients</td></tr>';
                }

            } catch (error) {
                console.error('Failed to fetch queue data:', error);
                if (!this.lastFetchError || Date.now() - this.lastFetchError > 60000) {
                    this.showNotification('Failed to load queue data. Please refresh if issue persists.', 'error');
                    this.lastFetchError = Date.now();
                }
            }
        },

        showNotification(message, type = 'success') {
            const notif = document.getElementById('notification');
            notif.textContent = message;
            notif.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white text-sm font-semibold z-50 shadow-lg transition-opacity duration-300 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
            notif.classList.remove('hidden');
            setTimeout(() => notif.style.opacity = '0', 2000);
            setTimeout(() => { notif.classList.add('hidden'); notif.style.opacity = '1'; }, 2500);
        },

        async makeRequest(url, options = {}) {
            try {
                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                try {
                    const tokenResponse = await fetch('/admin/csrf-token', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (tokenResponse.ok) {
                        const tokenData = await tokenResponse.json();
                        if (tokenData.token) {
                            csrfToken = tokenData.token;
                            const metaTag = document.querySelector('meta[name="csrf-token"]');
                            if (metaTag) {
                                metaTag.content = csrfToken;
                            }
                        }
                    }
                } catch (tokenError) {
                    console.warn('Could not fetch fresh CSRF token, using existing:', tokenError);
                }

                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const config = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    ...options
                };

                const response = await fetch(url, config);
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    if (response.status === 419) {
                        throw new Error('Session expired. Please refresh the page and try again.');
                    }
                    throw new Error('Server returned an invalid response. Please refresh the page.');
                }

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 419) {
                        throw new Error('Session expired. Please refresh the page and try again.');
                    }
                    throw new Error(data.message || `HTTP ${response.status}`);
                }

                if (data.success) {
                    this.showNotification(data.message || 'Action successful', 'success');
                    await this.fetchQueueData();
                    if ((url.includes('/next') || url.includes('/recall-now')) && data.now_serving?.queue_number) {
                        this.announceQueueNumber(data.now_serving.queue_number);
                    }
                } else {
                    throw new Error(data.message || 'Action failed');
                }

                return data;
            } catch (error) {
                console.error('Request error:', error);
                this.showNotification(error.message || 'Request failed', 'error');
                throw error;
            }
        },

        announceQueueNumber(queueNum) {
            const queueNumStr = String(queueNum).padStart(2, '0');
            const message = `NUMBER ${queueNumStr} PLEASE PROCEED TO WINDOW NUMBER ONE`;
            const speak = (text, onEnd) => {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.rate = 0.9;
                utterance.pitch = 1;
                utterance.volume = 1;
                if (onEnd) utterance.onend = onEnd;
                window.speechSynthesis.speak(utterance);
            };
            speak(message, () => speak(message, () => speak(message)));
        },

        markNextAsServed() {
            this.makeRequest("/admin/queue/next");
        },

        completeNowServing() {
            if (!this.nowServingActive) {
                this.showNotification('No one is currently being served', 'error');
                return;
            }
            this.makeRequest("/admin/queue/complete-now");
        },

        recallNowServing() {
            if (!this.nowServingActive) {
                this.showNotification('No one is currently being served', 'error');
                return;
            }
            this.makeRequest("/admin/queue/recall-now");
        },

        cancelNowServing() {
            if (!this.nowServingActive) {
                this.showNotification('No one is currently being served', 'error');
                return;
            }
            this.pendingActionName = 'cancel';
            this.pendingAction = "/admin/queue/cancel-now";
            this.openModal();
        },

        requeueNowServing() {
            if (!this.nowServingActive) {
                this.showNotification('No one is currently being served', 'error');
                return;
            }
            this.pendingActionName = 'requeue';
            this.pendingAction = "/admin/queue/requeue-now";
            this.openModal();
        },

        openModal() {
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.pendingAction = null;
            this.pendingActionName = '';
            this.isProcessing = false;
        },

        async confirmPendingAction() {
            if (!this.pendingAction || this.isProcessing) {
                return;
            }
            
            this.isProcessing = true;
            
            try {
                await this.makeRequest(this.pendingAction);
                this.closeModal();
            } catch (error) {
                console.error('Error executing action:', error);
            } finally {
                this.isProcessing = false;
            }
        },

        async recallFromRequeue(id, queueNumber) {
            if (this.nowServingActive) {
                this.showNotification('Please complete current client first.', 'error');
                return;
            }
            try {
                await this.makeRequest(`/admin/queue/serve-specific/${id}`);
                this.announceQueueNumber(queueNumber);
            } catch (error) {
                console.error('Failed to recall:', error);
            }
        }
    };
};
</script>
</head>
<body class="min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 sidebar-glass text-white flex flex-col fixed h-full z-40">
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
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="{{ route('admin.dashboard-main') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.dashboard-main') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </div>
                </a>
                
                @if(session('role') === 'admin')
                <a href="{{ route('admin.usermanagement') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.usermanagement') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </div>
                </a>
                @endif
                
                <a href="{{ route('admin.queuestatus') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.queuestatus') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-list"></i>
                        <span>Queue Status</span>
                    </div>
                </a>

                <a href="{{ route('admin.preregs') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.preregs') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-file-alt"></i>
                        <span>Pre-Registrations</span>
                    </div>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 flex flex-col ml-64" x-data="queueAppData()" @alpine:init="init()">
            <!-- Header -->
            <header class="header-glass sticky top-0 z-30 px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('img/philogo.png') }}" alt="PH Logo" class="w-10 h-10 object-contain drop-shadow-lg" />
                        <div>
                            <h1 class="text-xl font-georgia font-bold text-gray-900">North Caloocan City Hall</h1>
                            <p class="text-xs text-gray-600">Queue Status Management</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-georgia font-semibold text-gray-900">{{ session('username') }}</p>
                        <p id="currentDateTime" class="text-xs text-gray-500"></p>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <section class="px-8 py-4 flex flex-col gap-4">
                <!-- Now Serving & Controls - Compact Layout -->
                <div class="flex gap-4">
                    <!-- Now Serving Card - Takes most space -->
                    <div class="flex-1 now-serving-compact">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-lg font-georgia font-semibold flex items-center gap-2">
                                <i class="fas fa-user-check"></i>
                                Now Serving
                            </h2>
                        </div>
                        <div id="nowServing" class="text-white"></div>
                    </div>
                    
                    <!-- Control Buttons - Enhanced Call Next Button -->
                    <div class="flex gap-2">
                        <button @click="markNextAsServed()" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-8 py-4 rounded-xl font-bold transition flex items-center justify-center text-lg content-text whitespace-nowrap shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="fas fa-step-forward mr-2 text-xl"></i> Call Next
                        </button>
                    </div>
                </div>

                <!-- Filter Buttons - Compact -->
                <div class="bg-white rounded-xl shadow-md p-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-georgia font-semibold text-gray-700 flex items-center gap-2 text-sm">
                            <i class="fas fa-filter text-gray-500"></i>
                            Filter by Entry Type
                        </h3>
                        <div class="flex gap-2">
                            <button @click="setFilter('all')" 
                                    :class="{ 'active bg-gray-600 text-white': currentFilter === 'all', 'bg-gray-100 text-gray-700': currentFilter !== 'all' }"
                                    class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium content-text inline-flex items-center gap-2">
                                <i class="fas fa-list"></i>
                                All (<span x-text="counts.priority_total + counts.regular_total"></span>)
                            </button>
                            <button @click="setFilter('pre_registration')" 
                                    :class="{ 'active bg-blue-600 text-white': currentFilter === 'pre_registration', 'bg-blue-100 text-blue-700': currentFilter !== 'pre_registration' }"
                                    class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium content-text inline-flex items-center gap-2">
                                <i class="fas fa-qrcode"></i>
                                Pre-Reg (<span x-text="counts.priority_prereg + counts.regular_prereg"></span>)
                            </button>
                            <button @click="setFilter('direct')" 
                                    :class="{ 'active bg-purple-600 text-white': currentFilter === 'direct', 'bg-purple-100 text-purple-700': currentFilter !== 'direct' }"
                                    class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium content-text inline-flex items-center gap-2">
                                <i class="fas fa-desktop"></i>
                                Walk-in (<span x-text="counts.priority_kiosk + counts.regular_kiosk"></span>)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Queue Tables - Side by Side -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Priority Queue -->
                    <div class="bg-white rounded-xl shadow-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-lg font-georgia font-semibold text-gray-900">Priority Queue</h2>
                            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">
                                <i class="fas fa-star mr-1"></i>
                                <span x-text="counts.priority_total"></span> waiting
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b-2 border-gray-200">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Queue #</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Service</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Entry</th>
                                    </tr>
                                </thead>
                                <tbody id="priorityQueueBody" class="divide-y divide-gray-200 text-sm">
                                    <tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Regular Queue -->
                    <div class="bg-white rounded-xl shadow-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-lg font-georgia font-semibold text-gray-900">Regular Queue</h2>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                <i class="fas fa-users mr-1"></i>
                                <span x-text="counts.regular_total"></span> waiting
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b-2 border-gray-200">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Queue #</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Service</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Entry</th>
                                    </tr>
                                </thead>
                                <tbody id="regularQueueBody" class="divide-y divide-gray-200 text-sm">
                                    <tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Requeue Recall Panel -->
                <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-georgia font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-retweet text-orange-500"></i>
                            Requeued Clients (Recall List)
                        </h2>
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">
                            <span x-text="counts.requeued_total"></span> pending
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b-2 border-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Queue #</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Service</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody id="requeuedQueueBody" class="divide-y divide-gray-200 text-sm">
                                <tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Entry Type Legend - Compact -->
                <div class="bg-white rounded-xl shadow-md p-3">
                    <h3 class="font-georgia font-semibold text-gray-700 mb-2 text-sm">Entry Type Legend</h3>
                    <div class="flex flex-wrap gap-4 text-xs content-text">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-6 bg-blue-600 rounded"></div>
                            <span class="px-2 py-1 rounded-full text-xs text-white badge-prereg inline-flex items-center gap-1">
                                <i class="fas fa-qrcode"></i> Pre-Reg
                            </span>
                            <span class="text-gray-600">Pre-registered via QR</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-6 bg-purple-600 rounded"></div>
                            <span class="px-2 py-1 rounded-full text-xs text-white badge-kiosk inline-flex items-center gap-1">
                                <i class="fas fa-desktop"></i> Kiosk
                            </span>
                            <span class="text-gray-600">Walk-in via Kiosk</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Customer Details Modal -->
            <div x-show="showDetailsModal" 
                 x-cloak
                 @click.self="closeDetailsModal()" 
                 class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="modal-overlay fixed inset-0 transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-2xl shadow-2xl text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle w-full max-w-5xl"
                         @click.away="closeDetailsModal()">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-2xl font-bold flex items-center">
                                        <i class="fas fa-user-check mr-3"></i>
                                        Customer Details
                                    </h3>
                                    <p class="text-blue-100 mt-1 text-sm" x-show="nowServingData">
                                        Queue #<strong x-text="nowServingData?.queue_number"></strong> - 
                                        <strong x-text="nowServingData?.full_name"></strong>
                                    </p>
                                </div>
                                <button @click="closeDetailsModal()" class="text-white hover:bg-blue-800 rounded-full w-8 h-8 flex items-center justify-center transition">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="max-h-[70vh] overflow-y-auto p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 p-4 rounded-lg border">
                                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-id-card mr-2 text-blue-600"></i>
                                        Personal Information
                                    </h4>
                                    <ul class="space-y-2 text-sm">
                                        <li><strong>Full Name:</strong> <span x-text="nowServingData?.full_name || '-'"></span></li>
                                        <li><strong>Email:</strong> <span x-text="nowServingData?.email || '-'"></span></li>
                                        <li><strong>Age:</strong> <span x-text="nowServingData?.age || '-'"></span></li>
                                        <li><strong>Address:</strong> <span x-text="nowServingData?.address || '-'"></span></li>
                                        <li><strong>PWD ID:</strong> <span x-text="nowServingData?.pwd_id || '-'"></span></li>
                                        <li><strong>PWD Status:</strong> 
                                            <span x-text="nowServingData?.is_pwd ? 'Yes' : 'No'" 
                                                  :class="nowServingData?.is_pwd ? 'px-2 py-1 rounded-full text-xs bg-green-100 text-green-800' : 'px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800'"></span>
                                        </li>
                                        <li x-show="nowServingData?.priority_type"><strong>Priority Type:</strong> 
                                            <span x-text="nowServingData?.priority_type" class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800"></span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border">
                                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-cogs mr-2 text-green-600"></i>
                                        Request Details
                                    </h4>
                                    <ul class="space-y-2 text-sm">
                                        <li><strong>Service Type:</strong> <span x-text="nowServingData?.service_type || '-'"></span></li>
                                        <li><strong>Applicant Type:</strong> <span x-text="nowServingData?.applicant_type || '-'"></span></li>
                                        <li><strong>Copies:</strong> <span x-text="nowServingData?.number_of_copies || '-'"></span></li>
                                        <li><strong>Purpose:</strong> <span x-text="nowServingData?.purpose || '-'"></span></li>
                                        <li><strong>Govt ID Type:</strong> <span x-text="nowServingData?.govt_id_type || '-'"></span></li>
                                        <li><strong>Govt ID Number:</strong> <span x-text="nowServingData?.govt_id_number || '-'"></span></li>
                                        <li><strong>ID Issued At:</strong> <span x-text="nowServingData?.issued_at || '-'"></span></li>
                                        <li><strong>ID Issued On:</strong> <span x-text="nowServingData?.issued_on || '-'"></span></li>
                                        <li><strong>Entered At:</strong> <span x-text="nowServingData?.queue_entered_at || '-'"></span></li>
                                        <li><strong>Served At:</strong> <span x-text="nowServingData?.served_at || '-'"></span></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="space-y-4" x-show="nowServingData?.entry_type === 'pre_registration'">
                                <div class="border-t pt-4">
                                    <h4 class="font-bold text-gray-800 mb-3 text-lg">Property Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm bg-gray-50 p-4 rounded-lg">
                                        <div><strong>Land PIN:</strong> <span x-text="nowServingData?.pin_land || '-'"></span></div>
                                        <div><strong>Building PIN:</strong> <span x-text="nowServingData?.pin_building || '-'"></span></div>
                                        <div><strong>Machinery PIN:</strong> <span x-text="nowServingData?.pin_machinery || '-'"></span></div>
                                    </div>
                                </div>

                                <div class="border-t pt-4">
                                    <h4 class="font-bold text-gray-800 mb-4 flex items-center text-lg">
                                        <i class="fas fa-images mr-2 text-purple-600"></i>
                                        Uploaded Documents (Pre-Registration)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <template x-if="nowServingData?.documents?.owner_id_image">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-id-badge mr-2 text-purple-600"></i>
                                                    Owner's Government ID
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.owner_id_image)">
                                                    <img :src="nowServingData.documents.owner_id_image" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="Owner ID">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.spa_image">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-handshake mr-2 text-blue-600"></i>
                                                    Special Power of Attorney
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.spa_image)">
                                                    <img :src="nowServingData.documents.spa_image" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="SPA">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.rep_id_image">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-user-tie mr-2 text-teal-600"></i>
                                                    Representative's ID
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.rep_id_image)">
                                                    <img :src="nowServingData.documents.rep_id_image" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="Representative ID">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.tax_decl_form">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-file-alt mr-2 text-orange-600"></i>
                                                    Tax Declaration Form
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.tax_decl_form)">
                                                    <img :src="nowServingData.documents.tax_decl_form" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="Tax Declaration Form">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.title">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-book mr-2 text-red-600"></i>
                                                    Title
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.title)">
                                                    <img :src="nowServingData.documents.title" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="Title">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.tax_payment">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-receipt mr-2 text-green-600"></i>
                                                    Tax Payment
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.tax_payment)">
                                                    <img :src="nowServingData.documents.tax_payment" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="Tax Payment">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.latest_tax_decl">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-file-invoice-dollar mr-2 text-indigo-600"></i>
                                                    Latest Tax Declaration
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.latest_tax_decl)">
                                                    <img :src="nowServingData.documents.latest_tax_decl" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="Latest Tax Declaration">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.deed_of_sale">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-exchange-alt mr-2 text-pink-600"></i>
                                                    Deed of Sale
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.deed_of_sale)">
                                                    <img :src="nowServingData.documents.deed_of_sale" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="Deed of Sale">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.transfer_tax_receipt">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-money-bill-wave mr-2 text-yellow-600"></i>
                                                    Transfer Tax Receipt
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.transfer_tax_receipt)">
                                                    <img :src="nowServingData.documents.transfer_tax_receipt" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="Transfer Tax Receipt">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="nowServingData?.documents?.car_from_bir">
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h5 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                                                    <i class="fas fa-building mr-2 text-cyan-600"></i>
                                                    CAR from BIR
                                                </h5>
                                                <div class="cursor-zoom-in relative group" @click="openImageZoom(nowServingData.documents.car_from_bir)">
                                                    <img :src="nowServingData.documents.car_from_bir" 
                                                         class="max-h-[150px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                                         alt="CAR from BIR">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Notice for Kiosk Entries -->
                            <div class="border-t pt-4" x-show="nowServingData?.entry_type === 'direct'">
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                        <div>
                                            <p class="font-semibold text-blue-900">Walk-in Entry (Kiosk)</p>
                                            <p class="text-sm text-blue-800 mt-1">This client entered through the kiosk. No pre-uploaded documents available.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-200 bg-gray-50">
                            <div class="flex flex-col gap-3">
                                <div class="flex gap-3">
                                    <button @click="completeNowServing(); closeDetailsModal();" 
                                            class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center justify-center gap-2">
                                        <i class="fas fa-check-circle"></i>
                                        Complete Service
                                    </button>
                                    <button @click="recallNowServing(); closeDetailsModal();" 
                                            class="flex-1 px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition flex items-center justify-center gap-2">
                                        <i class="fas fa-redo-alt"></i>
                                        Recall Client
                                    </button>
                                </div>
                                <div class="flex gap-3">
                                    <button @click="requeueNowServing()" 
                                            class="flex-1 px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition flex items-center justify-center gap-2">
                                        <i class="fas fa-retweet"></i>
                                        Requeue Client
                                    </button>
                                    <button @click="cancelNowServing()" 
                                            class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center justify-center gap-2">
                                        <i class="fas fa-times-circle"></i>
                                        Cancel Service
                                    </button>
                                </div>
                                <button @click="closeDetailsModal()" 
                                        class="w-full px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Zoom Modal -->
            <div id="zoomModal" class="fixed inset-0 hidden z-50 overflow-hidden">
                <div class="flex items-center justify-center min-h-screen bg-black bg-opacity-90 p-4">
                    <div class="relative w-full h-full max-w-6xl max-h-[90vh] flex flex-col">
                        <div class="flex justify-between items-center text-white mb-2 px-2">
                            <h3 class="text-lg font-semibold">Zoom & Pan — Click and drag | Scroll to zoom</h3>
                            <button onclick="closeZoomModal()" class="text-white hover:text-gray-300 text-2xl font-bold">&times;</button>
                        </div>
                        <div id="zoomContainer" class="relative w-full h-full flex-1 flex items-center justify-center cursor-move">
                            <img id="zoomedImage" 
                                 class="max-w-none max-h-none opacity-0 transition-opacity duration-200" 
                                 style="transform-origin: center;">
                        </div>
                        <div class="flex justify-center gap-4 mt-2 text-white text-sm">
                            <button type="button" onclick="zoomIn()" class="px-3 py-1 bg-blue-600 rounded hover:bg-blue-700">+</button>
                            <button type="button" onclick="resetZoom()" class="px-3 py-1 bg-gray-600 rounded hover:bg-gray-700">Reset</button>
                            <button type="button" onclick="zoomOut()" class="px-3 py-1 bg-blue-600 rounded hover:bg-blue-700">−</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div x-show="showModal" 
                 x-cloak
                 @click.self="closeModal()" 
                 class="fixed inset-0 z-50 flex items-center justify-center modal-overlay"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-90">
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-georgia font-bold text-gray-900 mb-2">Confirm Action</h3>
                        <p class="text-gray-600 content-text">
                            Are you sure you want to <span class="font-semibold" x-text="pendingActionName"></span> the currently serving client?
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeModal()" 
                                :disabled="isProcessing"
                                class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition content-text"
                                :class="{ 'opacity-50 cursor-not-allowed': isProcessing }">
                            Cancel
                        </button>
                        <button @click="confirmPendingAction()" 
                                :disabled="isProcessing"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition content-text inline-flex items-center justify-center gap-2"
                                :class="{ 'opacity-50 cursor-not-allowed': isProcessing }">
                            <span x-show="!isProcessing">Confirm</span>
                            <span x-show="isProcessing" class="inline-flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notification Toast -->
            <div id="notification" class="hidden fixed top-4 right-4 px-4 py-2 rounded-lg text-white text-sm font-semibold z-50 shadow-lg transition-opacity duration-300"></div>
        </main>
    </div>

    <!-- Load Panzoom -->
    <script src="https://unpkg.com/@panzoom/panzoom@4.4.3/dist/panzoom.min.js"></script>
    <script>
        function closeZoomModal() {
            document.getElementById('zoomModal').classList.add('hidden');
            if (window.panzoomInstance) {
                window.panzoomInstance.dispose();
                window.panzoomInstance = null;
            }
        }

        function zoomIn() {
            if (window.panzoomInstance) window.panzoomInstance.zoomIn();
        }

        function zoomOut() {
            if (window.panzoomInstance) window.panzoomInstance.zoomOut();
        }

        function resetZoom() {
            if (window.panzoomInstance) window.panzoomInstance.reset();
        }

        document.getElementById('zoomModal').addEventListener('click', function(e) {
            if (e.target === this) closeZoomModal();
        });
    </script>
</body>
</html>
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
        .modal-overlay { background: rgba(0, 0, 0, 0.5); }
        .btn-disabled { opacity: 0.5; cursor: not-allowed; }
        [x-cloak] { display: none !important; }
    </style>

    <script>
        window.queueAppData = function() {
            return {
                currentDateTime: '',
                pendingAction: null,
                pendingActionName: '',
                nowServingActive: false,
                isProcessing: false,
                showModal: false,

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

                async fetchQueueData() {
                    try {
                        const res = await fetch('/admin/queue');
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        const data = await res.json();

                        const nowServingEl = document.getElementById('nowServing');
                        if (data.now_serving) {
                            nowServingEl.textContent = `Queue #${data.now_serving.queue_number} - ${data.now_serving.full_name}`;
                            nowServingEl.classList.add('text-blue-600', 'font-semibold');
                            document.getElementById('nowServingDetailsSection').classList.remove('hidden');
                            this.nowServingActive = true;

                            Object.entries({
                                detailId: data.now_serving.id,
                                detailQueueNumber: data.now_serving.queue_number,
                                detailFullName: data.now_serving.full_name,
                                detailServiceType: data.now_serving.service_type,
                                detailAge: data.now_serving.age,
                                detailNumberOfCopies: data.now_serving.number_of_copies,
                                detailPurpose: data.now_serving.purpose,
                                detailAddress: data.now_serving.address,
                                detailEmail: data.now_serving.email,
                                detailApplicantType: data.now_serving.applicant_type,
                                detailGovtIdType: data.now_serving.govt_id_type,
                                detailGovtIdNumber: data.now_serving.govt_id_number,
                                detailIssuedAt: data.now_serving.issued_at,
                                detailIssuedOn: data.now_serving.issued_on,
                                detailPinLand: data.now_serving.pin_land,
                                detailPinBuilding: data.now_serving.pin_building,
                                detailPinMachinery: data.now_serving.pin_machinery,
                                detailPwdId: data.now_serving.pwd_id,
                                detailEntryType: data.now_serving.entry_type,
                                detailEnteredAt: data.now_serving.queue_entered_at,
                                detailServedAt: data.now_serving.served_at,
                                detailStatus: data.now_serving.status,
                            }).forEach(([id, value]) => {
                                const el = document.getElementById(id);
                                if (el) el.textContent = value || '--';
                            });

                            const pwdBadge = document.getElementById("pwdStatusBadge");
                            if (pwdBadge) {
                                pwdBadge.textContent = data.now_serving.is_pwd ? "Yes" : "No";
                                pwdBadge.className = data.now_serving.is_pwd ? "px-2 py-1 rounded-full text-xs bg-green-100 text-green-800" : "px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800";
                            }

                            const priorityBadge = document.getElementById("priorityTypeBadge");
                            if (priorityBadge && data.now_serving.priority_type) {
                                const type = data.now_serving.priority_type;
                                priorityBadge.textContent = type;
                                priorityBadge.className = type === 'PWD' ? "px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800" : type === 'Senior' ? "px-2 py-1 rounded-full text-xs bg-green-100 text-green-800" : "px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800";
                            }
                        } else {
                            nowServingEl.textContent = 'No one being served';
                            nowServingEl.classList.remove('text-blue-600', 'font-semibold');
                            document.getElementById('nowServingDetailsSection').classList.add('hidden');
                            this.nowServingActive = false;
                        }

                        const priorityCount = data.priority?.length ?? 0;
                        const regularCount = data.regular?.length ?? 0;
                        document.getElementById("priorityCountDisplay").textContent = priorityCount;
                        document.getElementById("regularCountDisplay").textContent = regularCount;

                        const priorityBody = document.getElementById("priorityQueueBody");
                        if (priorityBody) {
                            priorityBody.innerHTML = priorityCount > 0 ? data.priority.map(p => `<tr class="queue-card hover:bg-gray-50"><td class="px-6 py-4">${p.queue_number}</td><td class="px-6 py-4">${p.name}</td><td class="px-6 py-4">${p.service_type}</td></tr>`).join('') : '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No one in priority queue</td></tr>';
                        }

                        const regularBody = document.getElementById("regularQueueBody");
                        if (regularBody) {
                            regularBody.innerHTML = regularCount > 0 ? data.regular.map(r => `<tr class="queue-card hover:bg-gray-50"><td class="px-6 py-4">${r.queue_number}</td><td class="px-6 py-4">${r.name}</td><td class="px-6 py-4">${r.service_type}</td></tr>`).join('') : '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No one in regular queue</td></tr>';
                        }
                    } catch (error) {
                        console.error('Failed to fetch queue data:', error);
                        this.showNotification('Failed to load queue data: ' + error.message, 'error');
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
                    const config = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        ...options
                    };

                    const response = await fetch(url, config);
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || `HTTP ${response.status}`);
                    }
                    
                    if (data.success) {
                        this.showNotification(data.message || 'Action successful', 'success');
                        await this.fetchQueueData();
                        if ((url === "/admin/queue/next" || url === "/admin/queue/recall-now") && data.now_serving?.queue_number) {
                            this.announceQueueNumber(data.now_serving.queue_number);
                        }
                    } else {
                        throw new Error(data.message || 'Action failed');
                    }
                    
                    return data;
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
                    console.log('cancelNowServing called');
                    if (!this.nowServingActive) {
                        this.showNotification('No one is currently being served', 'error');
                        return;
                    }
                    this.pendingActionName = 'cancel';
                    this.pendingAction = "/admin/queue/cancel-now";
                    console.log('Opening modal for cancel');
                    this.openModal(); 
                },
                
                requeueNowServing() {
                    console.log('requeueNowServing called');
                    if (!this.nowServingActive) {
                        this.showNotification('No one is currently being served', 'error');
                        return;
                    }
                    this.pendingActionName = 'requeue';
                    this.pendingAction = "/admin/queue/requeue-now";
                    console.log('Opening modal for requeue');
                    this.openModal(); 
                },
                
                openModal() {
                    console.log('openModal called');
                    this.showModal = true;
                    console.log('showModal set to:', this.showModal);
                },
                
                closeModal() {
                    console.log('closeModal called');
                    this.showModal = false;
                    this.pendingAction = null;
                    this.pendingActionName = '';
                    this.isProcessing = false;
                },
                
                async confirmPendingAction() {
                    console.log('confirmPendingAction called');
                    console.log('pendingAction:', this.pendingAction);
                    console.log('isProcessing:', this.isProcessing);
                    
                    if (!this.pendingAction || this.isProcessing) {
                        console.log('Exiting - no action or already processing');
                        return;
                    }
                    
                    this.isProcessing = true;
                    console.log('Set isProcessing to true');
                    
                    try {
                        console.log('Making request to:', this.pendingAction);
                        await this.makeRequest(this.pendingAction);
                        console.log('Request successful, closing modal');
                        this.closeModal();
                    } catch (error) {
                        console.error('Error executing action:', error);
                        this.showNotification(error.message || 'Action failed', 'error');
                    } finally {
                        console.log('Finally block - resetting isProcessing');
                        this.isProcessing = false;
                    }
                }
            };
        };
    </script>
</head>
<body class="min-h-screen">
    <div class="flex min-h-screen">
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
                
                <a href="{{ route('admin.mailbox') }}" 
                   class="nav-link block font-georgia text-white {{ request()->routeIs('admin.mailbox') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-envelope"></i>
                        <span>Mailbox</span>
                    </div>
                </a>
            </nav>
        </aside>
        
        <main class="flex-1 flex flex-col ml-64" x-data="queueAppData()" @alpine:init="init()">
            <header class="header-glass sticky top-0 z-30 px-8 py-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('img/philogo.png') }}" alt="PH Logo" class="w-12 h-12 object-contain drop-shadow-lg" />
                        <div>
                            <h1 class="text-2xl font-georgia font-bold text-gray-900">North Caloocan City Hall</h1>
                            <p class="text-sm text-gray-600">Queue Status Management</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-georgia font-semibold text-gray-900">{{ session('username') }}</p>
                        <p id="currentDateTime" class="text-xs text-gray-500"></p>
                    </div>
                </div>
            </header>
            
            <section class="px-8 py-6 flex flex-col gap-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-1 bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-xl font-georgia font-semibold mb-4">Now Serving</h2>
                        <div id="nowServing" class="text-gray-500">No one being served</div>
                        <div id="nowServingDetailsSection" class="hidden mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200 max-h-96 overflow-y-auto">
                            <h3 class="font-georgia font-semibold mb-3 sticky top-0 bg-gray-50">Client Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm content-text">
                                <div><strong>ID:</strong> <span id="detailId">--</span></div>
                                <div><strong>Queue #:</strong> <span id="detailQueueNumber">--</span></div>
                                <div><strong>Full Name:</strong> <span id="detailFullName">--</span></div>
                                <div><strong>Age:</strong> <span id="detailAge">--</span></div>
                                <div><strong>Service Type:</strong> <span id="detailServiceType">--</span></div>
                                <div><strong>Number of Copies:</strong> <span id="detailNumberOfCopies">--</span></div>
                                <div><strong>Purpose:</strong> <span id="detailPurpose">--</span></div>
                                <div><strong>Address:</strong> <span id="detailAddress">--</span></div>
                                <div><strong>Email:</strong> <span id="detailEmail">--</span></div>
                                <div><strong>Applicant Type:</strong> <span id="detailApplicantType">--</span></div>
                                <div><strong>Government ID Type:</strong> <span id="detailGovtIdType">--</span></div>
                                <div><strong>Government ID Number:</strong> <span id="detailGovtIdNumber">--</span></div>
                                <div><strong>ID Issued At:</strong> <span id="detailIssuedAt">--</span></div>
                                <div><strong>ID Issued On:</strong> <span id="detailIssuedOn">--</span></div>
                                <div><strong>Land PIN:</strong> <span id="detailPinLand">--</span></div>
                                <div><strong>Building PIN:</strong> <span id="detailPinBuilding">--</span></div>
                                <div><strong>Machinery PIN:</strong> <span id="detailPinMachinery">--</span></div>
                                <div><strong>PWD Status:</strong> <span id="pwdStatusBadge" class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">No</span></div>
                                <div><strong>PWD ID:</strong> <span id="detailPwdId">--</span></div>
                                <div><strong>Priority Type:</strong> <span id="priorityTypeBadge" class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">Regular</span></div>
                                <div><strong>Entry Type:</strong> <span id="detailEntryType">--</span></div>
                                <div><strong>Entered At:</strong> <span id="detailEnteredAt">--</span></div>
                                <div><strong>Served At:</strong> <span id="detailServedAt">--</span></div>
                                <div><strong>Status:</strong> <span id="detailStatus">--</span></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-4 w-48">
                        <button @click="markNextAsServed()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition flex items-center justify-center text-sm content-text">
                            <i class="fas fa-step-forward mr-1"></i> Next Number
                        </button>
                        <button @click="completeNowServing()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition flex items-center justify-center text-sm content-text" :class="{ 'btn-disabled': !nowServingActive }" :disabled="!nowServingActive">
                            <i class="fas fa-check-circle mr-1"></i> Complete Now
                        </button>
                        <button @click="recallNowServing()" class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-3 rounded-lg font-medium transition flex items-center justify-center text-sm content-text" :class="{ 'btn-disabled': !nowServingActive }" :disabled="!nowServingActive">
                            <i class="fas fa-redo-alt mr-1"></i> Recall Number
                        </button>
                        <button @click="requeueNowServing()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-3 rounded-lg font-medium transition flex items-center justify-center text-sm content-text" :class="{ 'btn-disabled': !nowServingActive }" :disabled="!nowServingActive">
                            <i class="fas fa-retweet mr-1"></i> Requeue Now
                        </button>
                        <button @click="cancelNowServing()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium transition flex items-center justify-center text-sm content-text" :class="{ 'btn-disabled': !nowServingActive }" :disabled="!nowServingActive">
                            <i class="fas fa-times-circle mr-1"></i> Cancel Now
                        </button>
                    </div>
                </div>
                
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-1 bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="font-georgia text-xl font-semibold text-gray-700">Priority Queue</h2>
                            <div class="text-sm text-gray-600 content-text"><span id="priorityCountDisplay">0</span> clients</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider content-text">Queue #</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider content-text">Name</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider content-text">Service</th>
                                    </tr>
                                </thead>
                                <tbody id="priorityQueueBody" class="bg-white divide-y divide-gray-200 content-text">
                                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex-1 bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="font-georgia text-xl font-semibold text-gray-700">Regular Queue</h2>
                            <div class="text-sm text-gray-600 content-text"><span id="regularCountDisplay">0</span> clients</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider content-text">Queue #</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider content-text">Name</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider content-text">Service</th>
                                    </tr>
                                </thead>
                                <tbody id="regularQueueBody" class="bg-white divide-y divide-gray-200 content-text">
                                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Modal (inside main Alpine component) -->
            <div x-show="showModal" 
                 x-cloak
                 class="fixed inset-0 z-[9999]"
                 style="display: none;">
                <div class="absolute inset-0 bg-black bg-opacity-50" @click="console.log('Overlay clicked'); !isProcessing && closeModal()"></div>
                <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-georgia font-semibold">Confirm Action</h3>
                            <button @click="console.log('X clicked'); closeModal()" class="text-gray-500 hover:text-gray-700 p-2" :disabled="isProcessing">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="mb-6 text-gray-700 content-text">Are you sure you want to <span x-text="pendingActionName"></span> this client?</p>
                        <div class="flex justify-end gap-3">
                            <button @click="console.log('Cancel clicked'); closeModal()" class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-sm font-medium transition content-text" :disabled="isProcessing" :class="{ 'opacity-50 cursor-not-allowed': isProcessing }">Cancel</button>
                            <button @click="console.log('Confirm clicked'); confirmPendingAction()" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition content-text flex items-center justify-center min-w-[100px]" :disabled="isProcessing" :class="{ 'opacity-50 cursor-not-allowed': isProcessing }">
                                <span x-show="!isProcessing">Confirm</span>
                                <span x-show="isProcessing" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="notification" class="fixed top-4 right-4 hidden px-4 py-2 rounded-lg text-white text-sm font-semibold z-50 shadow-lg transition-opacity duration-300 content-text">Action successful!</div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mailbox Submissions - Admin Panel</title>
    <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 50%, #e5e7eb 100%); }
        .sidebar-glass { background: rgba(27, 60, 83, 0.98); backdrop-filter: blur(20px); border-right: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 8px 0 32px rgba(0, 0, 0, 0.1); }
        .header-glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); }
        .nav-link { border-radius: 12px; padding: 12px 16px; margin: 4px 0; }
        .nav-link:hover { background: rgba(255, 255, 255, 0.1); }
        .nav-link.active { background: rgba(255, 255, 255, 0.15); border-left: 4px solid #F59E0B; font-weight: 600; }
        .queue-card { transition: all 0.3s ease; }
        .queue-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .badge-pending { @apply px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800; }
        .badge-submitted { @apply px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800; }
        .badge-completed { @apply px-2 py-1 rounded-full text-xs bg-green-100 text-green-800; }
        .badge-disapproved { @apply px-2 py-1 rounded-full text-xs bg-red-100 text-red-800; }
        .modal-overlay { background: rgba(0, 0, 0, 0.5); }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 sidebar-glass text-white flex flex-col fixed h-full z-40">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3 mb-4">
                    <img src="{{ asset('img/mainlogo.png') }}" alt="City Hall Logo" class="w-10 h-10 object-contain" />
                    <h1 class="text-xl font-bold">
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
                   class="nav-link block text-white {{ request()->routeIs('admin.dashboard-main') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </div>
                </a>
                
                @if(session('role') === 'admin')
                <a href="{{ route('admin.usermanagement') }}" 
                   class="nav-link block text-white {{ request()->routeIs('admin.usermanagement') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </div>
                </a>
                @endif
                
                <a href="{{ route('admin.queuestatus') }}" 
                   class="nav-link block text-white {{ request()->routeIs('admin.queuestatus') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-list"></i>
                        <span>Queue Status</span>
                    </div>
                </a>

                <a href="{{ route('admin.mailbox') }}" 
                   class="nav-link block text-white {{ request()->routeIs('admin.mailbox') ? 'active' : '' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-envelope"></i>
                        <span>Mailbox</span>
                    </div>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 flex flex-col ml-64">
            <header class="header-glass sticky top-0 z-30 px-8 py-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('img/philogo.png') }}" alt="PH Logo" class="w-12 h-12 object-contain drop-shadow-lg" />
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">North Caloocan City Hall</h1>
                            <p class="text-sm text-gray-600">IoT Mailbox Document Submissions</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">{{ session('username') }}</p>
                        <p id="currentDateTime" class="text-xs text-gray-500"></p>
                    </div>
                </div>
            </header>
            
            <section class="px-8 py-6">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-700">Mailbox Submissions</h2>
                        <div class="text-sm text-gray-600">
                            <span id="totalCountDisplay">0</span> submissions
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">PIN</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Submitted At</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="mailboxBody" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 hidden flex items-center justify-center z-50">
        <div class="modal-overlay absolute inset-0" onclick="closeModal()"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md relative z-10 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-xl font-semibold">Confirm Action</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p id="modalMessage" class="mb-6 text-gray-700">Are you sure?</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeModal()" class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-sm font-medium">
                    Cancel
                </button>
                <button onclick="confirmAction()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification" class="fixed top-4 right-4 hidden px-4 py-3 rounded-lg text-white text-sm font-semibold z-50 shadow-lg">
        Notification
    </div>

    <script>
        let pendingAction = null;

        function updateDateTime() {
            const now = new Date();
            document.getElementById('currentDateTime').textContent = now.toLocaleString("en-US", {
                weekday: 'short', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true
            });
        }

        async function fetchMailboxData() {
            try {
                const res = await fetch('/admin/mailbox/data');
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();

                document.getElementById("totalCountDisplay").textContent = data.length;

                const tbody = document.getElementById("mailboxBody");
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No submissions yet</td></tr>';
                    return;
                }

                tbody.innerHTML = data.map(s => {
                    let statusBadge = '';
                    if (s.status === 'completed') {
                        statusBadge = '<span class="badge-completed">Completed</span>';
                    } else if (s.status === 'disapproved') {
                        statusBadge = '<span class="badge-disapproved">Disapproved</span>';
                    } else if (s.status === 'submitted') {
                        statusBadge = '<span class="badge-submitted">Submitted</span>';
                    } else {
                        statusBadge = '<span class="badge-pending">Pending</span>';
                    }

                    const submittedAt = s.submitted_at ? new Date(s.submitted_at).toLocaleString() : '-';

                    let actions = '-';
                    if (s.status === 'submitted') {
                        actions = `
                          <div class="flex gap-2">
                            <button onclick="confirmApprove(${s.id}, '${s.full_name}')" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 text-xs rounded">Approve</button>
                            <button onclick="confirmDisapprove(${s.id}, '${s.full_name}')" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 text-xs rounded">Disapprove</button>
                          </div>
                        `;
                    }

                    return `
                      <tr class="queue-card hover:bg-gray-50">
                        <td class="px-6 py-4">${s.full_name}</td>
                        <td class="px-6 py-4"><code class="text-xs bg-gray-100 px-2 py-1 rounded">${s.pin_code}</code></td>
                        <td class="px-6 py-4">${s.service_type || '-'}</td>
                        <td class="px-6 py-4">${s.email || '-'}</td>
                        <td class="px-6 py-4 text-sm">${submittedAt}</td>
                        <td class="px-6 py-4">${statusBadge}</td>
                        <td class="px-6 py-4">${actions}</td>
                      </tr>
                    `;
                }).join('');
            } catch (error) {
                console.error('Error fetching data:', error);
                showNotification('Failed to load submissions', 'error');
            }
        }

        function confirmApprove(id, name) {
            document.getElementById('modalTitle').textContent = 'Approve Submission';
            document.getElementById('modalMessage').textContent = `Approve submission from ${name}?`;
            pendingAction = () => submitAction(id, 'approve');
            document.getElementById('confirmationModal').classList.remove('hidden');
        }

        function confirmDisapprove(id, name) {
            document.getElementById('modalTitle').textContent = 'Disapprove Submission';
            document.getElementById('modalMessage').textContent = `Disapprove submission from ${name}?`;
            pendingAction = () => submitAction(id, 'disapprove');
            document.getElementById('confirmationModal').classList.remove('hidden');
        }

        async function submitAction(id, action) {
            try {
                const endpoint = action === 'approve' ? `/admin/mailbox/${id}/approve` : `/admin/mailbox/${id}/disapprove`;
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });

                const data = await res.json();
                if (res.ok) {
                    showNotification(data.message || `Submission ${action}d successfully`);
                    closeModal();
                    await fetchMailboxData();
                } else {
                    showNotification(data.message || 'Action failed', 'error');
                }
            } catch (err) {
                console.error('Error:', err);
                showNotification('Action failed', 'error');
            }
        }

        function confirmAction() {
            if (pendingAction) pendingAction();
        }

        function closeModal() {
            document.getElementById('confirmationModal').classList.add('hidden');
        }

        function showNotification(message, type = 'success') {
            const toast = document.getElementById('notification');
            toast.textContent = message;
            toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold z-50 shadow-lg ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        // Initialize
        updateDateTime();
        setInterval(updateDateTime, 1000);
        fetchMailboxData();
        setInterval(fetchMailboxData, 30000);
    </script>
</body>
</html>
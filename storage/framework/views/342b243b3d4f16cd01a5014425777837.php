<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Management - Admin Panel</title>
    <link rel="icon" href="<?php echo e(asset('img/mainlogo.png')); ?>" type="image/png">
    <!-- Tailwind CSS -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <style>
        .font-georgia { 
            font-family: Georgia, 'Times New Roman', Times, serif; 
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
        
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
        }
        
        .user-card {
            transition: all 0.3s ease;
        }
        .user-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
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

        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 flex flex-col ml-64">
            <section class="px-8 py-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-georgia font-bold text-gray-900 mb-2">User Management</h2>
                        <p class="text-gray-600">Manage system accounts and permissions</p>
                    </div>
                    <?php if(session('role') === 'admin'): ?>
                    <button id="openAddModalBtn" class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-5 py-2.5 rounded-xl font-georgia font-semibold transition shadow-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Account
                    </button>
                    <?php else: ?>
                    <div class="text-sm text-gray-600 bg-yellow-100 px-4 py-2.5 rounded-lg flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 text-yellow-700"></i>
                        <span>View Only Access</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Search Bar -->
                <div class="mb-6">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" placeholder="Search by username..." class="pl-10 w-full p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm">
                    </div>
                </div>

                <!-- User List -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <?php if(session('role') === 'admin'): ?>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- User rows will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Empty State -->
                    <div id="emptyState" class="py-12 text-center hidden">
                        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-users text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No users found</h3>
                        <p class="text-gray-500">Try adjusting your search or add a new account</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
    
    <div id="modals"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const baseURL = "<?php echo e(route('admin.users.index')); ?>";
        const currentUserRole = "<?php echo e(session('role')); ?>";

        async function safeFetch(url, options = {}) {
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    },
                    credentials: 'same-origin'
                });

                const contentType = response.headers.get('content-type');
                if (contentType && !contentType.includes('application/json')) {
                    const text = await response.text();
                    if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                        console.error('HTML response:', text.substring(0, 200));
                        throw new Error('Session expired or server error');
                    }
                }

                if (!response.ok) {
                    let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData.message || Object.values(errorData.errors || {}).flat().join(', ') || errorMessage;
                    } catch (e) {
                        if (response.status === 404) errorMessage = 'Resource not found';
                        else if (response.status === 422) errorMessage = 'Invalid data provided';
                        else if (response.status === 500) errorMessage = 'Server error occurred';
                    }
                    throw new Error(errorMessage);
                }

                return await response.json();
            } catch (error) {
                console.error('Fetch error:', error);
                throw error;
            }
        }

        function loadUsers() {
            <?php if(session('role') === 'admin'): ?>
            safeFetch(baseURL)
                .then(data => {
                    console.log('Loaded users:', data);
                    const search = document.getElementById('searchInput').value.toLowerCase();
                    const filtered = data.filter(u => u.username.toLowerCase().includes(search));
                    const tbody = document.getElementById('userTableBody');
                    const emptyState = document.getElementById('emptyState');
                    
                    if (filtered.length === 0) {
                        tbody.innerHTML = '';
                        emptyState.classList.remove('hidden');
                        return;
                    }
                    
                    emptyState.classList.add('hidden');
                    tbody.innerHTML = filtered.map(u => `
                        <tr class="user-card hover:bg-gray-50 transition-all">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center">
                <img src="${u.role === 'admin' ? '<?php echo e(asset("img/admin.png")); ?>' : '<?php echo e(asset("img/staff.png")); ?>'}" 
                     alt="${u.role}" 
                     class="h-10 w-10 rounded-full object-cover object-center border-2 border-white shadow-sm">
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">${u.username}</div>
            </div>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="capitalize px-3 py-1 rounded-full text-xs font-semibold
            ${u.role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}">
            ${u.role.replace('_', ' ')}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
            Active
        </span>
    </td>
    <?php if(session('role') === 'admin'): ?>
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <div class="flex justify-end space-x-3">
            <button onclick="openChangePw('${u.id}', '${u.username}')" class="text-blue-600 hover:text-blue-900 flex items-center">
                <i class="fas fa-key mr-1"></i> Change
            </button>
            ${u.can_delete ? `
            <button onclick="confirmDelete('${u.id}', '${u.username}')" class="text-red-600 hover:text-red-900 flex items-center">
                <i class="fas fa-trash-alt mr-1"></i> Delete
            </button>
            ` : '<span class="text-gray-400 flex items-center"><i class="fas fa-lock mr-1"></i> Protected</span>'}
        </div>
    </td>
    <?php endif; ?>
</tr>
                    `).join('');
                })
                .catch(err => {
                    console.error('Load failed:', err);
                    const tbody = document.getElementById('userTableBody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-red-500">
                                <div class="text-lg font-georgia">Error loading users</div>
                                <div class="text-sm mt-2">${err.message}</div>
                            </td>
                        </tr>`;
                    
                    if (err.message.includes('expired')) {
                        alert('Session expired. Please log in again.');
                        window.location.href = '<?php echo e(route("adminhome")); ?>';
                    } else {
                        showNotification('Load failed: ' + err.message, 'error');
                    }
                });
            <?php else: ?>
            document.getElementById('userTableBody').innerHTML = `
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                        <div class="text-lg font-georgia">Staff Access Limited</div>
                        <div class="text-sm mt-2">Contact Admin to manage accounts</div>
                    </td>
                </tr>`;
            <?php endif; ?>
        }

        function openChangePw(id, username) {
            const html = `
                <div class="fixed inset-0 modal-overlay flex items-center justify-center z-50 animate-fadeIn">
                    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-md">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-georgia font-semibold">Change Password</h2>
                            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="mb-4 text-sm text-gray-600">For: <strong>${username}</strong></p>
                        <form onsubmit="event.preventDefault(); updatePassword('${id}');">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="newPw">
                                    New Password
                                </label>
                                <input type="password" id="newPw" placeholder="Enter new password"
                                       class="w-full border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       minlength="6" required />
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                    Cancel
                                </button>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>`;
            document.getElementById("modals").innerHTML = html;
        }

        function updatePassword(id) {
            const pw = document.getElementById('newPw').value;
            if (pw.length < 6) return showNotification('Password must be at least 6 characters', 'error');

            safeFetch(`${baseURL}/${id}/password`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password: pw })
            })
            .then(() => {
                closeModal();
                loadUsers();
                showNotification('Password updated successfully!', 'success');
            })
            .catch(err => {
                console.error('Password update error:', err);
                showNotification('Update failed: ' + err.message, 'error');
            });
        }

        function confirmDelete(id, username) {
            const html = `
                <div class="fixed inset-0 modal-overlay flex items-center justify-center z-50 animate-fadeIn">
                    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-md">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-georgia font-semibold">Delete Account</h2>
                            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="mb-6 text-gray-600">Are you sure you want to delete <strong>${username}</strong>? This action cannot be undone.</p>
                        <div class="flex justify-end gap-3">
                            <button onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                Cancel
                            </button>
                            <button onclick="deleteUser('${id}')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>`;
            document.getElementById("modals").innerHTML = html;
        }

        function deleteUser(id) {
            safeFetch(`${baseURL}/${id}`, { 
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(() => {
                closeModal();
                loadUsers();
                showNotification('User deleted successfully!', 'success');
            })
            .catch(err => {
                console.error('Delete error:', err);
                showNotification('Delete failed: ' + err.message, 'error');
            });
        }

        function closeModal() {
            document.getElementById("modals").innerHTML = "";
        }

        <?php if(session('role') === 'admin'): ?>
        document.getElementById('openAddModalBtn').addEventListener('click', () => {
            const html = `
                <div class="fixed inset-0 modal-overlay flex items-center justify-center z-50 animate-fadeIn">
                    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-md">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-georgia font-semibold">Add New Account</h2>
                            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form onsubmit="event.preventDefault(); createUser();">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="addUsername">
                                        Username
                                    </label>
                                    <input type="text" id="addUsername" placeholder="Enter username" class="w-full border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="addPassword">
                                        Password
                                    </label>
                                    <input type="password" id="addPassword" placeholder="Enter password (min 6)" class="w-full border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" minlength="6" required />
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="addRole">
                                        Role
                                    </label>
                                    <select id="addRole" class="w-full border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        <option value="" disabled selected>Select Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="staff">Staff</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                    Cancel
                                </button>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                    Create Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>`;
            document.getElementById("modals").innerHTML = html;
        });

        function createUser() {
            const username = document.getElementById('addUsername').value.trim();
            const password = document.getElementById('addPassword').value;
            const role = document.getElementById('addRole').value;

            if (password.length < 6) {
                return showNotification('Password must be at least 6 characters', 'error');
            }

            safeFetch(baseURL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password, role })
            })
            .then(() => {
                closeModal();
                loadUsers();
                showNotification('User created successfully!', 'success');
            })
            .catch(err => {
                showNotification('Create failed: ' + err.message, 'error');
            });
        }
        <?php endif; ?>

        document.getElementById('searchInput').addEventListener('input', loadUsers);
        window.onload = loadUsers;

        function showNotification(message, type = 'success') {
            const notif = document.createElement('div');
            notif.className = `fixed top-4 right-4 px-5 py-3 rounded-lg text-white text-sm font-medium z-50 shadow-lg transition-opacity duration-300 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.style.opacity = '0', 3000);
            setTimeout(() => notif.remove(), 3500);
        }
    </script>
</body>
</html><?php /**PATH C:\Users\kiosk\Documents\thesis\resources\views/Admin/usermanagement.blade.php ENDPATH**/ ?>
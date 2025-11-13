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
        .modal-overlay { background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(2px); }
        .checkbox-item { transition: all 0.2s ease; }
        .checkbox-item:hover { background: #f9fafb; }
        .checkbox-item input[type="checkbox"]:checked + label { font-weight: 600; color: #dc2626; }
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

    <!-- Disapproval Modal with Requirements Checklist -->
    <div id="disapprovalModal" class="fixed inset-0 hidden z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="modal-overlay fixed inset-0 transition-opacity" onclick="closeDisapprovalModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl shadow-2xl text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle w-full max-w-3xl">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            Disapprove Submission
                        </h3>
                        <p class="text-red-100 mt-1 text-sm" id="disapprovalSubtitle">Select incorrect or missing documents</p>
                    </div>
                    <button onclick="closeDisapprovalModal()" class="text-white hover:bg-red-800 rounded-full w-8 h-8 flex items-center justify-center transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="max-h-96 overflow-y-auto p-6">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <p class="font-semibold text-blue-900">Instructions</p>
                            <p class="text-sm text-blue-800 mt-1">Check all documents that are incorrect, incomplete, or missing. The applicant will receive an email listing these specific issues.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6" id="requirementsChecklist">
                    <!-- Common Requirements -->
                    <div>
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center text-lg border-b pb-2">
                            <i class="fas fa-file-alt text-indigo-600 mr-2"></i>
                            Common Required Documents
                        </h4>
                        <div class="space-y-2 pl-2">
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm">
                                <input type="checkbox" id="req1" name="incorrect_doc" value="Request for Issuance of Updated Tax Declaration form" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="req1" class="text-sm cursor-pointer">Request for Issuance of Updated Tax Declaration form</label>
                            </div>
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm">
                                <input type="checkbox" id="req2" name="incorrect_doc" value="Title (Certified True Xerox Copy)" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="req2" class="text-sm cursor-pointer">Title (Certified True Xerox Copy)</label>
                            </div>
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm">
                                <input type="checkbox" id="req3" name="incorrect_doc" value="Updated Real Property Tax Payment (Amilyar)" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="req3" class="text-sm cursor-pointer">Updated Real Property Tax Payment (Amilyar)</label>
                            </div>
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm">
                                <input type="checkbox" id="req4" name="incorrect_doc" value="Latest Tax Declaration (TD/OHA)" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="req4" class="text-sm cursor-pointer">Latest Tax Declaration (TD/OHA)</label>
                            </div>
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm">
                                <input type="checkbox" id="req5" name="incorrect_doc" value="Deed of Sale / Extra Judicial Settlement / Partition Agreement" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="req5" class="text-sm cursor-pointer">Deed of Sale / Extra Judicial Settlement / Partition Agreement</label>
                            </div>
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm">
                                <input type="checkbox" id="req6" name="incorrect_doc" value="Transfer Tax Receipt" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="req6" class="text-sm cursor-pointer">Transfer Tax Receipt</label>
                            </div>
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm">
                                <input type="checkbox" id="req7" name="incorrect_doc" value="Certificate Authorizing Registration (CAR) from BIR" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="req7" class="text-sm cursor-pointer">Certificate Authorizing Registration (CAR) from BIR</label>
                            </div>
                        </div>
                    </div>

                    <!-- Role-Specific Requirements -->
                    <div id="roleSpecificRequirements">
                        <!-- Will be populated dynamically -->
                    </div>

                    <!-- Other Reason -->
                    <div class="border-t pt-4">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center text-lg border-b pb-2">
                            <i class="fas fa-comment-dots text-amber-600 mr-2"></i>
                            Additional Notes (Optional)
                        </h4>
                        <div class="checkbox-item p-3 rounded-lg border-2 border-amber-200 bg-amber-50 hover:shadow-sm mb-3">
                            <input type="checkbox" id="enableOtherReason" class="mr-3 w-5 h-5 text-amber-600 cursor-pointer" onchange="toggleOtherReason()">
                            <label for="enableOtherReason" class="text-sm font-semibold text-amber-900 cursor-pointer">Add specific notes or other reasons</label>
                        </div>
                        <textarea id="otherReasonText" 
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none disabled:bg-gray-100 disabled:cursor-not-allowed" 
                                  rows="3" 
                                  placeholder="Describe other issues with the submission (e.g., 'Documents are not properly notarized', 'Photos are unclear', etc.)"
                                  disabled></textarea>
                    </div>
                </div>

                <!-- Selected Count -->
                <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-check-square mr-2 text-red-600"></i>
                        <span id="selectedCount">0</span> item(s) selected
                    </p>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-end gap-3">
                    <button onclick="closeDisapprovalModal()" 
                            class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button onclick="submitDisapproval()" 
                            id="submitDisapprovalBtn"
                            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        <i class="fas fa-paper-plane"></i>
                        Send Disapproval Email
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Approval Confirmation Modal -->
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
        let currentSubmissionId = null;
        let currentSubmissionData = null;

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
                            <button onclick="confirmApprove(${s.id}, '${s.full_name.replace(/'/g, "\\'")}')" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 text-xs rounded">Approve</button>
                            <button onclick='openDisapprovalModal(${JSON.stringify(s).replace(/'/g, "&apos;")})' 
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

        function openDisapprovalModal(submission) {
            currentSubmissionId = submission.id;
            currentSubmissionData = submission;
            
            // Update subtitle with applicant name
            document.getElementById('disapprovalSubtitle').textContent = `Reviewing submission from ${submission.full_name}`;
            
            // Populate role-specific requirements
            const roleSpecificDiv = document.getElementById('roleSpecificRequirements');
            let roleSpecificHTML = '';
            
            if (submission.applicant_type === 'owner') {
                roleSpecificHTML = `
                    <div>
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center text-lg border-b pb-2">
                            <i class="fas fa-user text-green-600 mr-2"></i>
                            Owner-Specific Documents
                        </h4>
                        <div class="space-y-2 pl-2">
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm bg-green-50">
                                <input type="checkbox" id="reqOwner" name="incorrect_doc" value="Owner's Valid ID" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="reqOwner" class="text-sm cursor-pointer">Owner's Valid ID (Government-issued photo ID)</label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (submission.applicant_type === 'representative') {
                roleSpecificHTML = `
                    <div>
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center text-lg border-b pb-2">
                            <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                            Representative-Specific Documents
                        </h4>
                        <div class="space-y-2 pl-2">
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm bg-blue-50">
                                <input type="checkbox" id="reqSPA" name="incorrect_doc" value="Special Power of Attorney (SPA)" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="reqSPA" class="text-sm cursor-pointer">Special Power of Attorney (SPA) - Must be notarized</label>
                            </div>
                            <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm bg-blue-50">
                                <input type="checkbox" id="reqRepID" name="incorrect_doc" value="Representative's Valid ID" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                <label for="reqRepID" class="text-sm cursor-pointer">Representative's Valid ID (Government-issued photo ID)</label>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            roleSpecificDiv.innerHTML = roleSpecificHTML;
            
            // Reset all selections
            document.querySelectorAll('input[name="incorrect_doc"]').forEach(cb => cb.checked = false);
            document.getElementById('enableOtherReason').checked = false;
            document.getElementById('otherReasonText').value = '';
            document.getElementById('otherReasonText').disabled = true;
            updateSelectedCount();
            
            document.getElementById('disapprovalModal').classList.remove('hidden');
        }

        function closeDisapprovalModal() {
            document.getElementById('disapprovalModal').classList.add('hidden');
            currentSubmissionId = null;
            currentSubmissionData = null;
        }

        function toggleOtherReason() {
            const checkbox = document.getElementById('enableOtherReason');
            const textarea = document.getElementById('otherReasonText');
            textarea.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                textarea.value = '';
            }
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('input[name="incorrect_doc"]:checked');
            const otherReasonEnabled = document.getElementById('enableOtherReason').checked;
            const count = checkedBoxes.length + (otherReasonEnabled ? 1 : 0);
            document.getElementById('selectedCount').textContent = count;
            
            // Enable/disable submit button
            const submitBtn = document.getElementById('submitDisapprovalBtn');
            if (count > 0) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        async function submitDisapproval() {
            const checkedBoxes = Array.from(document.querySelectorAll('input[name="incorrect_doc"]:checked'));
            const incorrectDocs = checkedBoxes.map(cb => cb.value);
            
            const otherReasonEnabled = document.getElementById('enableOtherReason').checked;
            const otherReasonText = document.getElementById('otherReasonText').value.trim();
            
            // Validation: Must have either checkboxes OR other reason
            if (incorrectDocs.length === 0 && !otherReasonEnabled) {
                showNotification('Please select at least one document or provide additional notes', 'error');
                return;
            }

            // If other reason is checked, text must be provided
            if (otherReasonEnabled && !otherReasonText) {
                showNotification('Please provide additional notes or uncheck the option', 'error');
                return;
            }

            // If only other reason is selected, ensure we have at least a placeholder in incorrectDocs
            const finalIncorrectDocs = incorrectDocs.length > 0 ? incorrectDocs : ['Other issues (see notes below)'];

            // Disable button and show loading
            const submitBtn = document.getElementById('submitDisapprovalBtn');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
            
            try {
                const res = await fetch(`/admin/mailbox/${currentSubmissionId}/disapprove`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        incorrect_documents: finalIncorrectDocs,
                        other_reason: otherReasonEnabled && otherReasonText ? otherReasonText : null
                    })
                });

                const data = await res.json();
                if (res.ok) {
                    showNotification(data.message || 'Submission disapproved successfully');
                    closeDisapprovalModal();
                    await fetchMailboxData();
                } else {
                    showNotification(data.message || 'Action failed', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                }
            } catch (err) {
                console.error('Error:', err);
                showNotification('Network error. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }
        }

        function confirmApprove(id, name) {
            document.getElementById('modalTitle').textContent = 'Approve Submission';
            document.getElementById('modalMessage').textContent = `Approve submission from ${name}? This will create a pre-registration and send a QR code to their email.`;
            pendingAction = () => submitAction(id, 'approve');
            document.getElementById('confirmationModal').classList.remove('hidden');
        }

        async function submitAction(id, action) {
            try {
                const endpoint = `/admin/mailbox/${id}/approve`;
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });

                const data = await res.json();
                if (res.ok) {
                    showNotification(data.message || 'Submission approved successfully');
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
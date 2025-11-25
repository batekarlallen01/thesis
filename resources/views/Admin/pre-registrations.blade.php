<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pre-Registrations - Admin Panel</title>
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
        .queue-card { transition: all 0.3s ease; cursor: pointer; }
        .queue-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .badge-pending { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; background-color: #fef3c7; color: #d97706; }
        .badge-approved { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; background-color: #dcfce7; color: #16a34a; }
        .badge-disapproved { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; background-color: #fee2e2; color: #dc2626; }
        .modal-overlay { background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(2px); }
        .checkbox-item { transition: all 0.2s ease; }
        .checkbox-item:hover { background: #f9fafb; }
        .checkbox-item input[type="checkbox"]:checked + label { font-weight: 600; color: #dc2626; }
        .cursor-zoom-in { cursor: zoom-in; }
        .cursor-zoom-in:hover img { transform: scale(1.05); transition: transform 0.2s; }
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
                   class="nav-link block text-white">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </div>
                </a>
                @if(session('role') === 'admin')
                <a href="{{ route('admin.usermanagement') }}" 
                   class="nav-link block text-white">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </div>
                </a>
                @endif
                <a href="{{ route('admin.queuestatus') }}" 
                   class="nav-link block text-white">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-list"></i>
                        <span>Queue Status</span>
                    </div>
                </a>
                <a href="{{ route('admin.preregs') }}" 
                   class="nav-link block text-white active">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-file-alt"></i>
                        <span>Pre-Registrations</span>
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
                            <p class="text-sm text-gray-600">Online Pre-Registration Review</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">{{ session('username') }}</p>
                        <p id="currentDateTime" class="text-xs text-gray-500"></p>
                    </div>
                </div>
            </header>

            <section class="px-8 py-6 space-y-4">
                <!-- Controls: Search & Filter -->
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-60">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Name or Email</label>
                        <input type="text" id="searchInput"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Type to search..." />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                        <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm" onchange="fetchPreregData()">
                            <option value="">Pending Only</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="disapproved">Disapproved</option>
                        </select>
                    </div>
                    <button onclick="fetchPreregData()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-700">Pre-Registration Submissions</h2>
                        <div class="text-sm text-gray-600">
                            Total: <strong><span id="totalCountDisplay">0</span></strong>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="preregBody" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- View Details Modal -->
    <div id="detailsModal" class="fixed inset-0 hidden z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="modal-overlay fixed inset-0 transition-opacity" onclick="closeDetailsModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl shadow-2xl text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle w-full max-w-5xl">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-bold flex items-center">
                                <i class="fas fa-user-check mr-3"></i>
                                Review Submission
                            </h3>
                            <p class="text-blue-100 mt-1 text-sm" id="detailsModalSubtitle">Submitted by <strong id="modalFullName">—</strong></p>
                        </div>
                        <button onclick="closeDetailsModal()" class="text-white hover:bg-blue-800 rounded-full w-8 h-8 flex items-center justify-center transition">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="max-h-[80vh] overflow-y-auto p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-id-card mr-2 text-blue-600"></i>
                                Personal Information
                            </h4>
                            <ul class="space-y-2 text-sm">
                                <li><strong>Full Name:</strong> <span id="detailFullName">-</span></li>
                                <li><strong>Email:</strong> <span id="detailEmail">-</span></li>
                                <li><strong>Age:</strong> <span id="detailAge">-</span></li>
                                <li><strong>Address:</strong> <span id="detailAddress">-</span></li>
                                <li><strong>PWD ID:</strong> <span id="detailPwdId">-</span></li>
                                <li><strong>Priority:</strong> 
                                    <span id="detailPriorityStatus" class="font-medium px-2 py-1 rounded-full text-xs"
                                          style="background-color: #e0f2fe; color: #0284c7;"></span>
                                </li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-cogs mr-2 text-green-600"></i>
                                Request Details
                            </h4>
                            <ul class="space-y-2 text-sm">
                                <li><strong>Service Type:</strong> <span id="detailServiceType">-</span></li>
                                <li><strong>Applicant Type:</strong> <span id="detailApplicantType">-</span></li>
                                <li><strong>Status:</strong> 
                                    <span id="detailStatusBadge" class="px-2 py-1 rounded-full text-xs font-medium"></span>
                                </li>
                                <li><strong>Copies:</strong> <span id="detailCopies">-</span></li>
                                <li><strong>Purpose:</strong> <span id="detailPurpose">-</span></li>
                                <li><strong>Submitted:</strong> <span id="detailCreatedAt">-</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-id-badge mr-2 text-purple-600"></i>
                                Owner's Government-Issued ID
                            </h4>
                            <div id="previewOwnerId" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[200px]">
                                <span class="text-gray-500">No image uploaded</span>
                            </div>
                        </div>
                        <div id="sectionSpa" class="border border-gray-200 rounded-lg p-4 hidden">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-handshake mr-2 text-blue-600"></i>
                                Special Power of Attorney (SPA)
                            </h4>
                            <div id="previewSpa" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[200px]">
                                <span class="text-gray-500">No image uploaded</span>
                            </div>
                        </div>
                        <div id="sectionRepId" class="border border-gray-200 rounded-lg p-4 hidden">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-user-tie mr-2 text-teal-600"></i>
                                Representative's Government-Issued ID
                            </h4>
                            <div id="previewRepId" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[200px]">
                                <span class="text-gray-500">No image uploaded</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-file-alt mr-2 text-orange-600"></i>
                                    Request for Issuance of Updated Tax Declaration Form
                                </h4>
                                <div id="previewTaxDeclForm" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[180px]">
                                    <span class="text-gray-500">No image uploaded</span>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-book mr-2 text-red-600"></i>
                                    Title (Certified True Xerox Copy)
                                </h4>
                                <div id="previewTitle" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[180px]">
                                    <span class="text-gray-500">No image uploaded</span>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-receipt mr-2 text-green-600"></i>
                                    Updated Real Property Tax Payment (Amilyar)
                                </h4>
                                <div id="previewTaxPayment" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[180px]">
                                    <span class="text-gray-500">No image uploaded</span>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-file-invoice-dollar mr-2 text-indigo-600"></i>
                                    Latest Tax Declaration (TD/OHA)
                                </h4>
                                <div id="previewLatestTaxDecl" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[180px]">
                                    <span class="text-gray-500">No image uploaded</span>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-exchange-alt mr-2 text-pink-600"></i>
                                    Deed of Sale / Extra Judicial Settlement / Partition Agreement
                                </h4>
                                <div id="previewDeedOfSale" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[180px]">
                                    <span class="text-gray-500">No image uploaded</span>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-money-bill-wave mr-2 text-yellow-600"></i>
                                    Transfer Tax Receipt
                                </h4>
                                <div id="previewTransferTaxReceipt" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[180px]">
                                    <span class="text-gray-500">No image uploaded</span>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-building mr-2 text-cyan-600"></i>
                                    Certificate Authorizing Registration (CAR) from BIR
                                </h4>
                                <div id="previewCarFromBir" class="flex justify-center p-4 bg-gray-50 rounded border border-dashed min-h-[180px]">
                                    <span class="text-gray-500">No image uploaded</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                    <button onclick="closeDetailsModal()" 
                            class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">
                        Close
                    </button>
                    <button id="btnDisapprove" onclick="openDisapprovalModalFromDetails()" 
                            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fas fa-times-circle"></i>
                        Disapprove
                    </button>
                    <button id="btnApprove" onclick="confirmApproveFromDetails()" 
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Zoom Modal -->
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

    <!-- Disapproval Modal -->
    <div id="disapprovalModal" class="fixed inset-0 hidden z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="modal-overlay fixed inset-0 transition-opacity" onclick="closeDisapprovalModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl shadow-2xl text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle w-full max-w-3xl">
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
                        <div id="roleSpecificRequirements"></div>
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
                                      placeholder="Describe other issues (e.g., 'Documents are not properly notarized', 'Photos are unclear', etc.)"
                                      disabled></textarea>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-check-square mr-2 text-red-600"></i>
                            <span id="selectedCount">0</span> item(s) selected
                        </p>
                    </div>
                </div>
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

    <!-- Load Panzoom -->
    <script src="https://unpkg.com/@panzoom/panzoom@4.4.3/dist/panzoom.min.js"></script>

    <script>
        let pendingAction = null;
        let currentSubmission = null;
        let panzoomInstance = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Update date/time display
            function updateDateTime() {
                const now = new Date();
                document.getElementById('currentDateTime').textContent = now.toLocaleString("en-US", {
                    weekday: 'short', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit', hour12: true
                });
            }

            // Fetch pre-registration data
            async function internalFetchPreregData() {
                try {
                    const status = document.getElementById('statusFilter').value;
                    const search = document.getElementById('searchInput').value.trim();

                    let url = '/api/service-requests';
                    const params = new URLSearchParams();
                    if (status) params.append('status', status);
                    if (search) params.append('search', search);
                    if ([...params].length > 0) url += '?' + params.toString();

                    const res = await fetch(url);
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const data = await res.json();

                    document.getElementById("totalCountDisplay").textContent = data.length;
                    const tbody = document.getElementById("preregBody");

                    if (data.length === 0) {
                        const reason = search ? "No matching results" :
                                      status ? `No ${status} submissions` : "No pending submissions";
                        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">${reason}</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = data.map(s => {
                        let statusBadge = '';
                        if (s.status === 'approved') {
                            statusBadge = '<span class="badge-approved">Approved</span>';
                        } else if (s.status === 'disapproved') {
                            statusBadge = '<span class="badge-disapproved">Disapproved</span>';
                        } else {
                            statusBadge = '<span class="badge-pending">Pending</span>';
                        }
                        const submittedAt = s.created_at ? new Date(s.created_at).toLocaleString() : '-';
                        const actions = s.status === 'pending' 
                            ? '<span class="text-gray-500">Review to act</span>' 
                            : '-';

                        return `
                          <tr class="queue-card hover:bg-gray-50 cursor-pointer" data-id="${s.id}">
                            <td class="px-6 py-4">${s.full_name}</td>
                            <td class="px-6 py-4 text-sm">${formatServiceType(s.service_type)}</td>
                            <td class="px-6 py-4">${s.applicant_type === 'owner' ? 'Owner' : 'Representative'}</td>
                            <td class="px-6 py-4">${s.email || '-'}</td>
                            <td class="px-6 py-4 text-sm">${submittedAt}</td>
                            <td class="px-6 py-4">${statusBadge}</td>
                            <td class="px-6 py-4">${actions}</td>
                          </tr>
                        `;
                    }).join('');
                } catch (error) {
                    console.error('Error fetching registrations:', error);
                    showNotification('Failed to load submissions.', 'error');
                }
            }

            window.fetchPreregData = internalFetchPreregData;

            // Format service type
            function formatServiceType(type) {
                const labels = {
                    'tax_declaration': 'Certified True Copy of Tax Declaration',
                    'no_improvement': 'Certification of No Improvement',
                    'property_holdings': 'Certification of Property Holdings',
                    'non_property_holdings': 'Certification of Non-property Holdings'
                };
                return labels[type] || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }

            // Open details modal
            window.openDetailsModal = async function(id) {
                const modal = document.getElementById('detailsModal');
                modal.classList.remove('hidden');
                try {
                    const res = await fetch(`/api/service-requests/${id}`);
                    if (!res.ok) throw new Error('Failed to load');
                    const data = await res.json();
                    currentSubmission = data;

                    document.getElementById('modalFullName').textContent = data.full_name;
                    document.getElementById('detailFullName').textContent = data.full_name;
                    document.getElementById('detailEmail').textContent = data.email;
                    document.getElementById('detailAge').textContent = data.age;
                    document.getElementById('detailAddress').textContent = data.address;
                    document.getElementById('detailPwdId').textContent = data.pwd_id || '-';
                    document.getElementById('detailPriorityStatus').textContent = data.priority_status;
                    document.getElementById('detailServiceType').textContent = formatServiceType(data.service_type);
                    document.getElementById('detailApplicantType').textContent = data.applicant_type === 'owner' ? 'Owner' : 'Representative';
                    document.getElementById('detailCopies').textContent = data.number_of_copies;
                    document.getElementById('detailPurpose').textContent = data.purpose;
                    document.getElementById('detailCreatedAt').textContent = data.formatted_created_at;

                    const statusBadge = document.getElementById('detailStatusBadge');
                    statusBadge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    statusBadge.className = '';
                    if (data.status === 'approved') {
                        statusBadge.classList.add('bg-green-100', 'text-green-800');
                    } else if (data.status === 'disapproved') {
                        statusBadge.classList.add('bg-red-100', 'text-red-800');
                    } else {
                        statusBadge.classList.add('bg-yellow-100', 'text-yellow-800');
                    }

                    const isRep = data.applicant_type === 'representative';
                    document.getElementById('sectionSpa').classList.toggle('hidden', !isRep);
                    document.getElementById('sectionRepId').classList.toggle('hidden', !isRep);

                    const fieldToElementId = {
                        'owner_id_image': 'previewOwnerId',
                        'spa_image': 'previewSpa',
                        'rep_id_image': 'previewRepId',
                        'tax_decl_form': 'previewTaxDeclForm',
                        'title': 'previewTitle',
                        'tax_payment': 'previewTaxPayment',
                        'latest_tax_decl': 'previewLatestTaxDecl',
                        'deed_of_sale': 'previewDeedOfSale',
                        'transfer_tax_receipt': 'previewTransferTaxReceipt',
                        'car_from_bir': 'previewCarFromBir'
                    };

                    Object.keys(data.documents).forEach(field => {
                        const elementId = fieldToElementId[field];
                        const preview = document.getElementById(elementId);
                        if (!preview) return;
                        if (data.documents[field]) {
                            preview.innerHTML = `
                                <div class="cursor-zoom-in relative group">
                                    <img src="${data.documents[field]}" 
                                         class="max-h-[180px] mx-auto rounded shadow-md transition-transform duration-200 group-hover:scale-105"
                                         onclick="openImageZoom('${data.documents[field]}')"
                                         alt="${field.replace('_', ' ')}">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all pointer-events-none"></div>
                                </div>`;
                        } else {
                            preview.innerHTML = '<span class="text-gray-500">No image uploaded</span>';
                        }
                    });

                    // Hide action buttons if not pending
                    const approveBtn = document.getElementById('btnApprove');
                    const disapproveBtn = document.getElementById('btnDisapprove');

                    if (data.status !== 'pending') {
                        approveBtn.classList.add('hidden');
                        disapproveBtn.classList.add('hidden');
                    } else {
                        approveBtn.classList.remove('hidden');
                        disapproveBtn.classList.remove('hidden');
                    }

                } catch (err) {
                    console.error('Error loading details:', err);
                    alert('Failed to load submission details.');
                    closeDetailsModal();
                }
            };

            window.closeDetailsModal = function () {
                document.getElementById('detailsModal').classList.add('hidden');
                currentSubmission = null;
            };

            window.confirmApproveFromDetails = function () {
                if (currentSubmission) {
                    confirmApprove(currentSubmission.id, currentSubmission.full_name);
                    closeDetailsModal();
                }
            };

            window.openDisapprovalModalFromDetails = function () {
                if (currentSubmission) {
                    openDisapprovalModal(currentSubmission);
                    closeDetailsModal();
                }
            };

            // Click row to open modal
            document.getElementById('preregBody').addEventListener('click', function(e) {
                const row = e.target.closest('tr');
                if (!row) return;
                const id = row.dataset.id;
                if (id && !e.target.closest('button')) {
                    openDetailsModal(id);
                }
            });

            // Image Zoom Functions
            window.openImageZoom = function(imageSrc) {
                const modal = document.getElementById('zoomModal');
                const img = document.getElementById('zoomedImage');
                if (panzoomInstance) panzoomInstance.dispose();
                img.style.transform = 'scale(1) translate(0, 0)';
                img.src = '';
                img.classList.remove('opacity-100');
                img.classList.add('opacity-0');
                modal.classList.remove('hidden');
                img.onload = function () {
                    img.classList.remove('opacity-0');
                    img.classList.add('opacity-100');
                    panzoomInstance = panzoom(document.getElementById('zoomContainer'), {
                        contain: 'outside',
                        maxScale: 5,
                        minScale: 0.3,
                        zoomSpeed: 0.8,
                        panOnlyWhenZoomed: true,
                        cursor: 'move'
                    });
                };
                img.src = imageSrc;
                window.addEventListener('keydown', handleEscapeKey);
            };

            window.closeZoomModal = function() {
                document.getElementById('zoomModal').classList.add('hidden');
                if (panzoomInstance) panzoomInstance.dispose();
                window.removeEventListener('keydown', handleEscapeKey);
            };

            function handleEscapeKey(e) {
                if (e.key === 'Escape') closeZoomModal();
            }

            window.zoomIn = function() { if (panzoomInstance) panzoomInstance.zoomIn(); };
            window.zoomOut = function() { if (panzoomInstance) panzoomInstance.zoomOut(); };
            window.resetZoom = function() { if (panzoomInstance) panzoomInstance.reset(); };

            document.getElementById('zoomModal').addEventListener('click', function(e) {
                if (e.target === this) closeZoomModal();
            });

            // Disapproval Flow
            let currentSubmissionId = null;

            window.openDisapprovalModal = function(submission) {
                currentSubmissionId = submission.id;
                document.getElementById('disapprovalSubtitle').textContent = `Reviewing submission from ${submission.full_name}`;
                const roleSpecificDiv = document.getElementById('roleSpecificRequirements');
                let roleSpecificHTML = '';

                if (submission.applicant_type === 'owner') {
                    roleSpecificHTML = `
                        <div><h4 class="font-bold text-gray-800 mb-3 flex items-center text-lg border-b pb-2">
                            <i class="fas fa-user text-green-600 mr-2"></i>Owner-Specific Documents</h4>
                            <div class="space-y-2 pl-2">
                                <div class="checkbox-item p-3 rounded-lg border border-gray-200 hover:shadow-sm bg-green-50">
                                    <input type="checkbox" id="reqOwner" name="incorrect_doc" value="Owner's Valid ID" class="mr-3 w-5 h-5 text-red-600 cursor-pointer" onchange="updateSelectedCount()">
                                    <label for="reqOwner" class="text-sm cursor-pointer">Owner's Valid ID (Government-issued photo ID)</label>
                                </div>
                            </div>
                        </div>`;
                } else if (submission.applicant_type === 'representative') {
                    roleSpecificHTML = `
                        <div><h4 class="font-bold text-gray-800 mb-3 flex items-center text-lg border-b pb-2">
                            <i class="fas fa-user-tie text-blue-600 mr-2"></i>Representative-Specific Documents</h4>
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
                        </div>`;
                }
                roleSpecificDiv.innerHTML = roleSpecificHTML;
                document.querySelectorAll('input[name="incorrect_doc"]').forEach(cb => cb.checked = false);
                document.getElementById('enableOtherReason').checked = false;
                document.getElementById('otherReasonText').value = '';
                document.getElementById('otherReasonText').disabled = true;
                updateSelectedCount();
                document.getElementById('disapprovalModal').classList.remove('hidden');
            };

            window.closeDisapprovalModal = function() {
                document.getElementById('disapprovalModal').classList.add('hidden');
                currentSubmissionId = null;
            };

            window.toggleOtherReason = function() {
                const checkbox = document.getElementById('enableOtherReason');
                const textarea = document.getElementById('otherReasonText');
                textarea.disabled = !checkbox.checked;
                if (!checkbox.checked) textarea.value = '';
                updateSelectedCount();
            };

            window.updateSelectedCount = function() {
                const checkedBoxes = document.querySelectorAll('input[name="incorrect_doc"]:checked');
                const otherReasonEnabled = document.getElementById('enableOtherReason').checked;
                const count = checkedBoxes.length + (otherReasonEnabled ? 1 : 0);
                document.getElementById('selectedCount').textContent = count;
                document.getElementById('submitDisapprovalBtn').disabled = count === 0;
            };

            window.submitDisapproval = async function() {
                const checkedBoxes = Array.from(document.querySelectorAll('input[name="incorrect_doc"]:checked'));
                const incorrectDocs = checkedBoxes.map(cb => cb.value);
                const otherReasonEnabled = document.getElementById('enableOtherReason').checked;
                const otherReasonText = document.getElementById('otherReasonText').value.trim();

                if (incorrectDocs.length === 0 && !otherReasonEnabled) {
                    showNotification('Please select at least one issue or provide notes.', 'error');
                    return;
                }
                if (otherReasonEnabled && !otherReasonText) {
                    showNotification('Please enter additional notes or uncheck the option.', 'error');
                    return;
                }

                const finalIncorrectDocs = incorrectDocs.length > 0 ? incorrectDocs : ['Other issues'];
                const submitBtn = document.getElementById('submitDisapprovalBtn');
                const originalHTML = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';

                try {
                    const res = await fetch(`/api/pre-registration/${currentSubmissionId}/disapprove`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            incorrect_documents: finalIncorrectDocs,
                            other_reason: otherReasonEnabled ? otherReasonText : null
                        })
                    });

                    const data = await res.json();
                    if (res.ok) {
                        showNotification(data.message || 'Submission disapproved successfully');
                        closeDisapprovalModal();
                        await fetchPreregData();
                    } else {
                        showNotification(data.message || 'Action failed', 'error');
                    }
                } catch (err) {
                    console.error('Error:', err);
                    showNotification('Network error. Please try again.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                }
            };

            window.confirmApprove = function(id, name) {
                document.getElementById('modalTitle').textContent = 'Approve Submission';
                document.getElementById('modalMessage').textContent = `Approve pre-registration from ${name}? A QR code will be generated and sent via email.`;
                pendingAction = () => submitAction(id, 'approve');
                document.getElementById('confirmationModal').classList.remove('hidden');
            };

            window.submitAction = async function(id, action) {
                try {
                    const endpoint = `/api/pre-registration/${id}/approve`;
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (res.ok) {
                        showNotification(data.message || 'Approved successfully');
                        closeModal();
                        await fetchPreregData();
                    } else {
                        showNotification(data.message || 'Action failed', 'error');
                    }
                } catch (err) {
                    console.error('Error:', err);
                    showNotification('Action failed', 'error');
                }
            };

            window.confirmAction = function() {
                if (pendingAction) pendingAction();
            };

            window.closeModal = function() {
                document.getElementById('confirmationModal').classList.add('hidden');
            };

            window.showNotification = function(message, type = 'success') {
                const toast = document.getElementById('notification');
                toast.textContent = message;
                toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold z-50 shadow-lg ${
                    type === 'success' ? 'bg-green-600' : 'bg-red-600'
                }`;
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 3000);
            };

            // Initialize
            updateDateTime();
            setInterval(updateDateTime, 1000);
            fetchPreregData();
            setInterval(fetchPreregData, 30000);
        });
    </script>
</body>
</html>
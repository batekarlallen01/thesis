<?php

use App\Http\Controllers\PreRegistrationController;
use App\Http\Controllers\KioskEntryController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MailboxSubmissionController;
use App\Http\Controllers\UserController;


// Public routes
Route::get('/admin', [LoginController::class, 'showLoginForm'])->name('adminhome');
Route::post('/login', [LoginController::class, 'login'])->name('login');

// TEMPORARY DEBUG ROUTE - Remove after fixing
Route::get('/debug-tables', [DashboardController::class, 'debugTables']);

// Protected admin routes
Route::middleware(['admin.auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        $role = session('role');
        if ($role === 'admin' || $role === 'super_admin') {
            return redirect()->route('admin.dashboard-main');
        } elseif ($role === 'staff') {
            return redirect()->route('admin.dashboard-staff');
        }
        return redirect()->route('adminhome');
    })->name('dashboard');

    // Dashboard Routes with Controller
    Route::get('/dashboard-main', [DashboardController::class, 'index'])->name('dashboard-main');
    Route::get('/dashboard-stats', [DashboardController::class, 'getDashboardStats'])->name('dashboard-stats');

    Route::get('/dashboard-staff', function () {
        return view('Admin.dashboard');
    })->name('dashboard-staff');

    Route::get('/usermanagement', function () {
        return view('Admin.usermanagement');
    })->name('usermanagement');

    // User Management API Routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/queuemanagement', function () {
        return view('Admin.queuemanagement');
    })->name('queuemanagement');

    Route::get('/queuestatus', function () {
        return view('Admin.queuestatus');
    })->name('queuestatus');

    // ============================================================================
    // QUEUE MANAGEMENT ROUTES
    // ============================================================================
    Route::get('/queue', [QueueController::class, 'getQueueData'])->name('queue.data');
    Route::post('/queue/next', [QueueController::class, 'markNextAsServed'])->name('queue.next');
    Route::post('/queue/complete-now', [QueueController::class, 'completeNowServing'])->name('queue.complete');
    Route::post('/queue/cancel-now', [QueueController::class, 'cancelNowServing'])->name('queue.cancel');
    Route::post('/queue/requeue-now', [QueueController::class, 'requeueNowServing'])->name('queue.requeue');
    Route::post('/queue/recall-now', [QueueController::class, 'recallNowServing'])->name('queue.recall');
    Route::get('/queue/statistics', [QueueController::class, 'getStatistics'])->name('queue.statistics');

    // ============================================================================
    // KIOSK ENTRY MANAGEMENT ROUTES (Admin)
    // ============================================================================
    Route::get('/kiosk-entries', [KioskEntryController::class, 'index']);
    Route::get('/kiosk-entries/{id}', [KioskEntryController::class, 'show']);

    // ============================================================================
    // MAILBOX SUBMISSION ROUTES (Admin)
    // ============================================================================
    Route::get('/mailbox', function () {
        return view('Admin.mailbox');
    })->name('mailbox');
    
    Route::get('/mailbox/data', [MailboxSubmissionController::class, 'index'])->name('mailbox.data');
    Route::get('/mailbox-submissions', [MailboxSubmissionController::class, 'index'])->name('mailbox-submissions.index');
    Route::get('/mailbox-submissions/{id}', [MailboxSubmissionController::class, 'show'])->name('mailbox-submissions.show');
    Route::post('/mailbox/{id}/approve', [MailboxSubmissionController::class, 'approveMail'])->name('mailbox.approve');
    Route::post('/mailbox/{id}/disapprove', [MailboxSubmissionController::class, 'disapproveMail'])->name('mailbox.disapprove');

    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ============================================================================
// ONLINE SERVICES (Public)
// ============================================================================
Route::get('/', function () {return view('User.Online.landingpage'); })->name('User.landingpage');
Route::get('/services', function () {return view('User.Online.services'); })->name('services');
Route::get('/pre-regform', function () {return view('User.Online.pre-regform'); })->name('form');

// Pre-Registration API Routes
Route::post('/api/pre-registration', [PreRegistrationController::class, 'store']);
Route::get('/api/service-requests', [PreRegistrationController::class, 'index']);

// ============================================================================
// KIOSK SERVICES (Public)
// ============================================================================
Route::get('/kiosk', function () {return view('User.Kiosk.kioskhome'); })->name('User.kioskhome');
Route::get('/kioskform', function () {return view('User.Kiosk.kioskapplicationform'); })->name('kioskform');

// Kiosk Entry API Route (Public - for kiosk submission)
Route::post('/api/kiosk/entry', [KioskEntryController::class, 'store']);

// ============================================================================
// QR CODE ENTRY ROUTES (Public)
// ============================================================================

// Display / QR scanner page
Route::get('/queue/scanner', [\App\Http\Controllers\QREntryController::class, 'scannerPage'])->name('queue.scanner');
Route::get('/live-queue', function () {return view('queue.live-queue'); })->name('queue.live-queue');
// Public queue display endpoint
Route::get('/api/queue/display', [QueueController::class, 'getQueueData'])->name('queue.display-data');

// API endpoints for QR processing
Route::post('/api/qr/validate', [\App\Http\Controllers\QREntryController::class, 'validateQR']);
Route::post('/api/qr/entry', [\App\Http\Controllers\QREntryController::class, 'processQREntry']);

// Legacy QR scan page (keep for backwards compatibility with email links)
Route::get('/queue/scan/{token}', function ($token) {
    $preReg = \App\Models\PreRegistration::where('qr_token', $token)->first();
    if (!$preReg) {
        abort(404, 'Invalid or expired QR code.');
    }
    return view('queue.scan', compact('preReg'));
})->name('queue.scan');

// ============================================================================
// MAILBOX SUBMISSION ROUTES (Public)
// ============================================================================
Route::get('/mailform', function () {
    return view('User.Online.mailform');
})->name('mailform');

// ============================================================================
// MAILBOX SUBMISSION API ROUTES (Public - No CSRF but WITH ESP32 Auth)
// ============================================================================

// Public submission (web form - no auth needed)
//Route::post('/api/mailbox-submission', [MailboxSubmissionController::class, 'store']);

// ESP32 PIN verification (requires device token)
//Route::middleware(['esp32.token'])->group(function () {
    //Route::post('/api/verify-pin', [MailboxSubmissionController::class, 'verifyPin']);
//);

// Test printer
Route::get('/test-printer', [App\Http\Controllers\ReceiptPrintController::class, 'testPrint']);

//////
Route::get('/check-tables', function () {
    // Check pre_registrations table
    $preRegColumns = DB::select("DESCRIBE pre_registrations");
    
    // Check queue table
    $queueColumns = DB::select("DESCRIBE queue");
    
    // Check mailbox_submissions table
    $mailboxColumns = DB::select("DESCRIBE mailbox_submissions");
    
    // Get sample data
    $samplePreReg = DB::table('pre_registrations')->first();
    $sampleQueue = DB::table('queue')->first();
    $sampleMailbox = DB::table('mailbox_submissions')->first();
    
    return response()->json([
        'pre_registrations' => [
            'columns' => $preRegColumns,
            'sample_data' => $samplePreReg
        ],
        'queue' => [
            'columns' => $queueColumns,
            'sample_data' => $sampleQueue
        ],
        'mailbox_submissions' => [
            'columns' => $mailboxColumns,
            'sample_data' => $sampleMailbox
        ]
    ]);
});
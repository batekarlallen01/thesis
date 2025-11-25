<?php

use App\Http\Controllers\PreRegistrationController;
use App\Http\Controllers\KioskEntryController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

// Admin Login
Route::get('/admin', [LoginController::class, 'showLoginForm'])->name('adminhome');
Route::post('/login', [LoginController::class, 'login'])->name('login');

// Queue Cutoff Check (Public)
Route::get('/queue/check', [QueueController::class, 'checkQueueStatus']);

// ============================================================================
// PROTECTED ADMIN ROUTES
// ============================================================================
Route::middleware(['admin.auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard Routes
    Route::get('/dashboard', function () {
        $role = session('role');
        if ($role === 'admin' || $role === 'super_admin') {
            return redirect()->route('admin.dashboard-main');
        } elseif ($role === 'staff') {
            return redirect()->route('admin.dashboard-staff');
        }
        return redirect()->route('adminhome');
    })->name('dashboard');
    
    Route::get('/dashboard-main', [DashboardController::class, 'index'])->name('dashboard-main');
    Route::get('/dashboard-stats', [DashboardController::class, 'getDashboardStats'])->name('dashboard-stats');
    Route::get('/dashboard-staff', function () {
        return view('Admin.dashboard');
    })->name('dashboard-staff');

    // User Management Routes
    Route::get('/usermanagement', function () {
        return view('Admin.usermanagement');
    })->name('usermanagement');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Queue Management Routes
    Route::get('/queuemanagement', function () {
        return view('Admin.queuemanagement');
    })->name('queuemanagement');
    Route::get('/queuestatus', function () {
        return view('Admin.queuestatus');
    })->name('queuestatus');
    Route::get('/queue', [QueueController::class, 'getQueueData'])->name('queue.data');
    Route::post('/queue/next', [QueueController::class, 'markNextAsServed'])->name('queue.next');
    Route::post('/queue/complete-now', [QueueController::class, 'completeNowServing'])->name('queue.complete');
    Route::post('/queue/cancel-now', [QueueController::class, 'cancelNowServing'])->name('queue.cancel');
    Route::post('/queue/requeue-now', [QueueController::class, 'requeueNowServing'])->name('queue.requeue');
    Route::post('/queue/recall-now', [QueueController::class, 'recallNowServing'])->name('queue.recall');
    Route::post('/queue/serve-specific/{id}', [QueueController::class, 'serveSpecific'])->name('queue.serve-specific');
    Route::get('/queue/statistics', [QueueController::class, 'getStatistics'])->name('queue.statistics');

    // CSRF Token Helper
    Route::get('/csrf-token', function () {
        return response()->json(['token' => csrf_token()]);
    });

    // Pre-Registration Review (Admin)
    Route::get('/pre-registrations', function () {
        return view('Admin.pre-registrations');
    })->name('preregs');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ============================================================================
// ONLINE SERVICES (Public)
// ============================================================================
Route::get('/', function () {
    return view('User.Online.landingpage');
})->name('User.landingpage');

Route::get('/services', function () {
    return view('User.Online.services');
})->name('services');

Route::get('/pre-regform', function () {
    return view('User.Online.pre-regform');
})->name('form');

// Pre-Registration API Routes
Route::post('/api/pre-registration', [PreRegistrationController::class, 'store']);
Route::get('/api/service-requests', [PreRegistrationController::class, 'index']);
Route::get('/api/service-requests/{id}', [PreRegistrationController::class, 'show']);
Route::post('/api/pre-registration/{id}/approve', [PreRegistrationController::class, 'approve'])->middleware('admin.auth');
Route::post('/api/pre-registration/{id}/disapprove', [PreRegistrationController::class, 'disapprove'])->middleware('admin.auth');

// ============================================================================
// KIOSK SERVICES (Public)
// ============================================================================
Route::get('/kiosk', function () {
    return view('User.Kiosk.kioskhome');
})->name('User.kioskhome');

Route::get('/kioskform', function () {
    return view('User.Kiosk.kioskapplicationform');
})->name('kioskform');

Route::post('/api/kiosk/entry', [KioskEntryController::class, 'store']);

// ============================================================================
// QR CODE ENTRY & QUEUE DISPLAY (Public)
// ============================================================================
Route::get('/queue/scanner', [\App\Http\Controllers\QREntryController::class, 'scannerPage'])->name('queue.scanner');
Route::post('/api/qr/validate', [\App\Http\Controllers\QREntryController::class, 'validateQR']);
Route::post('/api/qr/entry', [\App\Http\Controllers\QREntryController::class, 'processQREntry']);

// Legacy QR scan page (backwards compatibility)
Route::get('/queue/scan/{token}', function ($token) {
    $preReg = \App\Models\PreRegistration::where('qr_token', $token)->first();
    if (!$preReg) {
        abort(404, 'Invalid or expired QR code.');
    }
    return view('queue.scan', compact('preReg'));
})->name('queue.scan');

// Live Queue Display
Route::get('/live-queue', function () {
    return view('queue.live-queue');
})->name('queue.live-queue');

Route::get('/api/queue/display', [QueueController::class, 'getQueueData'])->name('queue.display-data');
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('Admin.adminhome');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Debug: Log the incoming request
        Log::info('Login attempt', [
            'username' => $request->username,
            'has_password' => !empty($request->password)
        ]);

        // Validate input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        // Find admin by username
        $admin = Admin::where('username', $request->username)->first();

        // Debug: Check if admin exists
        if (!$admin) {
            Log::warning('Login failed - Admin not found', ['username' => $request->username]);
            
            return back()
                ->withErrors(['username' => 'Username not found.'])
                ->withInput($request->only('username'));
        }

        // Debug: Check password
        $passwordMatch = Hash::check($request->password, $admin->password);
        Log::info('Password check', [
            'username' => $request->username,
            'match' => $passwordMatch
        ]);

        // Check if password matches
        if (!$passwordMatch) {
            Log::warning('Login failed - Incorrect password', ['username' => $request->username]);
            
            return back()
                ->withErrors(['password' => 'Incorrect password.'])
                ->withInput($request->only('username'));
        }

        // Store admin info in session (IMPORTANT: must include admin_id)
        Session::put('admin_id', $admin->id);
        Session::put('username', $admin->username);
        Session::put('role', $admin->role);
        Session::put('logged_in', true);

        // Save session explicitly
        session()->save();

        // Debug: Check if session was saved
        Log::info('Session created', [
            'admin_id' => session('admin_id'),
            'username' => session('username'),
            'role' => session('role')
        ]);

        // Regenerate session to prevent fixation
        $request->session()->regenerate();

        Log::info('Login successful', [
            'admin_id' => $admin->id,
            'username' => $admin->username,
            'role' => $admin->role
        ]);

        return redirect()->route('admin.dashboard');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Log::info('User logged out', ['username' => session('username')]);
        
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('adminhome')->with('success', 'Logged out successfully.');
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is logged in
        if (!session()->has('admin_id') || !session()->has('role')) {
            // If it's an AJAX request, return JSON error
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized. Please log in.'], 401);
            }
            
            // Otherwise redirect to login
            return redirect()->route('adminhome')->with('error', 'Please log in first.');
        }

        return $next($request);
    }
}
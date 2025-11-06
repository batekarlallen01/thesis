<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateESP32Token
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-API-Token');
        
        $validToken = env('ESP32_API_TOKEN');
        
        if (!$validToken || $token !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized device'
            ], 401);
        }
        
        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimit
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->attributes->get('api_key');
        $key = 'api:' . ($apiKey?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'statusCode' => 429,
                'message' => 'Too many requests',
                'name' => 'rate_limit_exceeded',
            ], 429)->withHeaders([
                'Retry-After' => RateLimiter::availableIn($key),
                'X-RateLimit-Limit' => 10,
                'X-RateLimit-Remaining' => RateLimiter::remaining($key, 10),
            ]);
        }

        RateLimiter::hit($key, 1); // 1 second decay

        return $next($request);
    }
}

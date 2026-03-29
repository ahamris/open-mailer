<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'statusCode' => 401,
                'message' => 'Missing API key',
                'name' => 'missing_api_key',
            ], 401);
        }

        $apiKey = ApiKey::findByRawKey($token);

        if (!$apiKey) {
            return response()->json([
                'statusCode' => 403,
                'message' => 'Invalid API key',
                'name' => 'invalid_api_key',
            ], 403);
        }

        $apiKey->update(['last_used_at' => now()]);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}

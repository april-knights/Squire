<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token || $token !== config('services.squire_api_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken()) {
            Auth::shouldUse('api');
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Invalid token'], 401);
            }
        }

        return $next($request);
    }
}

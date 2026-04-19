<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only knights with security.pkey = 1 (Admin) may access /admin routes.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || $user->security !== 1) {
            session()->flash('error', 'You do not have permission to access the admin area.');
            return redirect('/');
        }

        return $next($request);
    }
}
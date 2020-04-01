<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class LogoutDeleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::check() && Auth::user()->delflg) {
            Auth::logout();

            return redirect()->route('login');
        }

        return $next($request);
    }
}

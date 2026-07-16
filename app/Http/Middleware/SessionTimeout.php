<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity_time');
            $timeout = config('session.lifetime') * 60; // Convert minutes to seconds

            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Session expired',
                        'redirect' => route('login')
                    ], 401);
                }

                return redirect()->route('login')
                    ->with('warning', 'Your session has expired. Please login again.');
            }

            // Update last activity time
            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}

<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user is logged in and if the user’s role matches
        if (!Auth::check() || Auth::user()->role !== $role) {
            // If not, redirect to the login page or error page
            return redirect()->route('login')->with('error', 'You are not authorized to access this page.');
        }

        // If the role matches, proceed to the next request
        return $next($request);
    }
}

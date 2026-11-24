<?php

namespace App\Http\Middleware;

use App\Support\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $token = $request->bearerToken() ?: $request->cookie('isaiah_api_token');
        $user = ApiToken::resolve($token);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication is required.'], 401);
        }

        if ($role) {
            $hasRolesMethod = method_exists($user, 'hasRole');
            $isAdmin = $hasRolesMethod && ($user->hasRole('admin') || $user->id === 1);

            if (!$isAdmin) {
                if ($role === 'customer') {
                    // All non-provider registered users have customer privileges
                    $isProvider = $hasRolesMethod && $user->hasRole('provider');
                    if ($isProvider) {
                        return response()->json(['success' => false, 'message' => 'You are not authorized to access this resource.'], 403);
                    }
                } elseif (!$hasRolesMethod || !$user->hasRole($role)) {
                    return response()->json(['success' => false, 'message' => 'You are not authorized to access this resource.'], 403);
                }
            }
        }

        $request->setUserResolver(fn () => $user);
        return $next($request);
    }
}

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

        if ($role && (!$user->hasRole($role))) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to access this resource.'], 403);
        }

        $request->setUserResolver(fn () => $user);
        return $next($request);
    }
}
